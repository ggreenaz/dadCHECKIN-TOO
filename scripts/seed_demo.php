<?php
/**
 * Demo Data Seeder — Southwick-Tolland-Granville Regional School District
 *
 * 1. Pulls real staff from Active Directory → creates hosts
 * 2. Creates 120 realistic visitors (parents, vendors, applicants, etc.)
 * 3. Inserts 30 days of visit history with realistic patterns
 * 4. Leaves 8–12 active check-ins for "Live Logs" demo
 *
 * Run: php scripts/seed_demo.php
 */

define('ROOT', dirname(__DIR__));
$dbCfg = require ROOT . '/config/database.local.php';

$pdo = new PDO(
    "mysql:host={$dbCfg['host']};port={$dbCfg['port']};dbname={$dbCfg['database']};charset=utf8mb4",
    $dbCfg['username'],
    $dbCfg['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

$orgId = 1;

// ─────────────────────────────────────────────────────────────
// 0. Wipe existing demo data
// ─────────────────────────────────────────────────────────────
echo "Clearing existing demo data…\n";
// Delete custom field values for this org's visits
$pdo->exec("DELETE cfv FROM custom_field_values cfv
            JOIN visits v ON cfv.visit_id = v.visit_id
            WHERE v.organization_id = $orgId");
// Collect visitor_ids used only by this org before deleting visits
$orphanVisitors = $pdo->query(
    "SELECT DISTINCT v.visitor_id FROM visits v
     WHERE v.organization_id = $orgId
       AND v.visitor_id NOT IN (
           SELECT visitor_id FROM visits WHERE organization_id != $orgId
       )"
)->fetchAll(PDO::FETCH_COLUMN);
$pdo->exec("DELETE FROM visits WHERE organization_id = $orgId");
if (!empty($orphanVisitors)) {
    $ids = implode(',', array_map('intval', $orphanVisitors));
    $pdo->exec("DELETE FROM visitors WHERE visitor_id IN ($ids)");
}
$pdo->exec("DELETE FROM hosts WHERE organization_id = $orgId");

// ─────────────────────────────────────────────────────────────
// 1. Map AD departments → local department_ids
// ─────────────────────────────────────────────────────────────
$deptRows = $pdo->query("SELECT department_id, name FROM departments ORDER BY name")->fetchAll();
$deptMap  = [];
foreach ($deptRows as $r) {
    $deptMap[strtolower($r['name'])] = (int)$r['department_id'];
}

// Fuzzy lookup: given an AD dept string, return best matching local dept_id
function matchDept(string $adDept, array $deptMap): ?int
{
    if (!$adDept) return null;
    $lower = strtolower(trim($adDept));

    // Direct match
    if (isset($deptMap[$lower])) return $deptMap[$lower];

    // Keyword mapping
    $keywords = [
        'admin'       => 'administration',
        'faculty'     => 'main office',
        'principal'   => 'administration',
        'guidance'    => 'guidance counseling',
        'counseling'  => 'guidance counseling',
        'nurse'       => 'health office / nurse',
        'health'      => 'health office / nurse',
        'finance'     => 'business office / finance',
        'business'    => 'business office / finance',
        'special ed'  => 'special education',
        'sped'        => 'special education',
        'tech'        => 'technology department',
        'library'     => 'library / media center',
        'media'       => 'library / media center',
        'art'         => 'fine arts',
        'music'       => 'fine arts',
        'athletics'   => 'athletics',
        'transport'   => 'transportation',
        'facilities'  => 'facilities & maintenance',
        'maintenance' => 'facilities & maintenance',
        'security'    => 'security / campus safety',
        'food'        => 'food services',
        'hr'          => 'human resources',
        'human'       => 'human resources',
        'psychology'  => 'school psychology',
        'math'        => 'mathematics department',
        'science'     => 'science department',
        'english'     => 'english department',
        'world lang'  => 'world languages department',
        'social stud' => 'social studies department',
        'career'      => 'career & technical education',
        'student sup' => 'student support services',
        'attendance'  => 'attendance office',
        'admissions'  => 'admissions / enrollment',
        'enrollment'  => 'admissions / enrollment',
    ];

    foreach ($keywords as $needle => $target) {
        if (str_contains($lower, $needle) && isset($deptMap[$target])) {
            return $deptMap[$target];
        }
    }

    // Fall back to main office
    return $deptMap['main office'] ?? null;
}

// ─────────────────────────────────────────────────────────────
// 2. Pull staff from LDAP → insert as hosts
// ─────────────────────────────────────────────────────────────
echo "Connecting to LDAP…\n";
$ldap = ldap_connect('ldap://10.1.55.33');
ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
ldap_set_option($ldap, LDAP_OPT_NETWORK_TIMEOUT, 10);

if (!ldap_bind($ldap, 'ldapsearch@stg.stgrsd.org', 'T011@nd!')) {
    die("LDAP bind failed\n");
}
echo "LDAP connected.\n";

$baseDn = 'ou=stg,dc=stg,dc=stgrsd,dc=org';
$filter = '(&(objectClass=user)(objectCategory=person)(!(userAccountControl:1.2.840.113556.1.4.803:=2))(mail=*@stgrsd.org)(givenName=*)(sn=*))';
$attrs  = ['givenName','sn','displayName','mail','department','title','telephoneNumber','mobile'];

$result  = @ldap_search($ldap, $baseDn, $filter, $attrs, 0, 400);
$entries = ldap_get_entries($ldap, $result);
ldap_unbind($ldap);

echo "Pulled {$entries['count']} staff from AD.\n";

// Collect clean staff records
$staffPool = [];
for ($i = 0; $i < $entries['count']; $i++) {
    $e     = $entries[$i];
    $first = trim($e['givenname'][0] ?? '');
    $last  = trim($e['sn'][0] ?? '');
    $email = strtolower(trim($e['mail'][0] ?? ''));
    if (!$first || !$last || !$email) continue;

    $staffPool[] = [
        'first'  => $first,
        'last'   => $last,
        'name'   => "$first $last",
        'email'  => $email,
        'dept'   => trim($e['department'][0] ?? ''),
        'title'  => trim($e['title'][0] ?? ''),
        'phone'  => trim($e['telephonenumber'][0] ?? ($e['mobile'][0] ?? '')),
    ];
}

// Shuffle and take up to 60 for hosts (diverse departments)
shuffle($staffPool);

// Try to get representation across departments
$hostsByDept = [];
$hostsToInsert = [];
foreach ($staffPool as $s) {
    $deptId = matchDept($s['dept'], $deptMap);
    $deptKey = $deptId ?? 0;
    if (!isset($hostsByDept[$deptKey])) $hostsByDept[$deptKey] = 0;
    if ($hostsByDept[$deptKey] >= 12) continue; // max 12 per dept bucket
    if (count($hostsToInsert) >= 55)  continue;
    $hostsByDept[$deptKey]++;
    $hostsToInsert[] = array_merge($s, ['dept_id' => $deptId]);
}

// Sort by name
usort($hostsToInsert, fn($a,$b) => strcmp($a['last'], $b['last']));

$insertHost = $pdo->prepare(
    "INSERT INTO hosts (organization_id, name, email, phone, department_id, active)
     VALUES (?, ?, ?, ?, ?, 1)"
);

$hostIds = [];
foreach ($hostsToInsert as $h) {
    $phone = $h['phone'] ?: null;
    $insertHost->execute([$orgId, $h['name'], $h['email'], $phone, $h['dept_id']]);
    $hostIds[] = (int)$pdo->lastInsertId();
}
echo "Inserted " . count($hostIds) . " hosts from AD.\n";

// ─────────────────────────────────────────────────────────────
// 3. Create 120 realistic visitors
// ─────────────────────────────────────────────────────────────
$firstNames = [
    // Parents / guardians
    'Amanda','Brian','Carol','Daniel','Elizabeth','Frank','Grace','Henry',
    'Isabella','James','Karen','Lawrence','Margaret','Nathan','Olivia','Patrick',
    'Rachel','Steven','Teresa','Victor','Wendy','Xavier','Yvonne','Zachary',
    'Alice','Robert','Sandra','Michael','Linda','William','Barbara','Richard',
    'Dorothy','Thomas','Ruth','Charles','Sharon','Christopher','Laura','Paul',
    // Vendors / contractors
    'Aaron','Blake','Cole','Derek','Ethan','Fiona','Glenn','Heather',
    'Ivan','Janet','Kevin','Leslie','Mark','Nancy','Oscar','Pamela',
    'Quinn','Randy','Stacy','Tim','Ursula','Vernon','Whitney','Yolanda',
    // Job applicants / community
    'Adrian','Brenda','Calvin','Diana','Eugene','Felicia','Gerald','Holly',
    'Irene','Jerome','Katherine','Leonard','Monica','Nelson','Ophelia','Preston',
    'Queenie','Roland','Sylvia','Travis','Uma','Valerie','Walter','Xiomara',
];

$lastNames = [
    'Adams','Baker','Brown','Campbell','Carter','Chen','Clark','Collins',
    'Cooper','Davis','Edwards','Evans','Flores','Garcia','Green','Hall',
    'Harris','Hernandez','Hill','Jackson','Johnson','Jones','King','Lee',
    'Lewis','Lopez','Martin','Martinez','Miller','Mitchell','Moore','Morris',
    'Nelson','Nguyen','Parker','Patel','Perez','Phillips','Roberts','Robinson',
    'Rodriguez','Sanchez','Scott','Smith','Taylor','Thomas','Thompson','Torres',
    'Turner','Walker','White','Williams','Wilson','Wood','Wright','Young',
    'Anderson','Bailey','Bennett','Brooks','Bryant','Butler','Coleman','Dixon',
    'Ford','Foster','Freeman','Gray','Griffin','Hayes','Henderson','Howard',
    'Hughes','James','Jenkins','Kelly','Kim','Long','Mason','McDonald',
    'Morgan','Murphy','Murray','Nash','Owens','Powell','Price','Reed',
    'Richardson','Russell','Shaw','Simmons','Simpson','Spencer','Stone','Sullivan',
    'Ward','Warren','Webb','Wells','West','Wheeler','Woods','Alexander',
];

// Area codes plausible for western MA
$areaCodes = ['413','860','508','774'];

function fakePhone(array $areaCodes): string
{
    $area   = $areaCodes[array_rand($areaCodes)];
    $prefix = rand(200,999);
    $line   = str_pad(rand(0,9999), 4, '0', STR_PAD_LEFT);
    return "($area) $prefix-$line";
}

// Visitor types for notes diversity
$visitorTypes = [
    'parent'    => 60,   // weight
    'vendor'    => 15,
    'applicant' => 10,
    'state'     => 5,
    'community' => 10,
];

$typePool = [];
foreach ($visitorTypes as $type => $weight) {
    for ($w = 0; $w < $weight; $w++) $typePool[] = $type;
}

shuffle($firstNames);
shuffle($lastNames);

$usedNames = [];
$visitors  = [];

for ($i = 0; $i < 120; $i++) {
    do {
        $first = $firstNames[array_rand($firstNames)];
        $last  = $lastNames[array_rand($lastNames)];
        $key   = "$first $last";
    } while (isset($usedNames[$key]));

    $usedNames[$key] = true;
    $type    = $typePool[array_rand($typePool)];
    $email   = strtolower($first[0] . $last) . rand(1,99) . match($type) {
        'vendor'    => '@vendor.com',
        'applicant' => '@gmail.com',
        'state'     => '@ct.gov',
        'community' => '@yahoo.com',
        default     => '@gmail.com',
    };

    $visitors[] = [
        'first' => $first,
        'last'  => $last,
        'email' => $email,
        'phone' => fakePhone($areaCodes),
        'type'  => $type,
    ];
}

$insertVisitor = $pdo->prepare(
    "INSERT INTO visitors (first_name, last_name, email, phone, created_at)
     VALUES (?, ?, ?, ?, NOW())"
);

$visitorIds = [];
foreach ($visitors as $v) {
    $insertVisitor->execute([$v['first'], $v['last'], $v['email'], $v['phone']]);
    $visitorIds[] = [(int)$pdo->lastInsertId(), $v['type']];
}
echo "Inserted " . count($visitorIds) . " visitors.\n";

// ─────────────────────────────────────────────────────────────
// 4. Load reason IDs
// ─────────────────────────────────────────────────────────────
$reasons = $pdo->query(
    "SELECT reason_id, label FROM visit_reasons WHERE organization_id = $orgId AND active = 1"
)->fetchAll();

// Map reason labels to visitor types for realism
$reasonsByType = [
    'parent'    => [1,2,3,4,5,6,9,10],  // parent-teacher, pick-up, counseling, etc.
    'vendor'    => [7],
    'applicant' => [5],
    'state'     => [5,6],
    'community' => [9,10,5],
];

$reasonIdSet = array_column($reasons, 'reason_id');

function pickReason(string $type, array $reasonsByType, array $allIds): int
{
    $pool = $reasonsByType[$type] ?? $allIds;
    $pool = array_filter($pool, fn($id) => in_array($id, $allIds));
    if (empty($pool)) return $allIds[array_rand($allIds)];
    $pool = array_values($pool);
    return $pool[array_rand($pool)];
}

// ─────────────────────────────────────────────────────────────
// 5. Visit notes templates
// ─────────────────────────────────────────────────────────────
$notesPool = [
    'parent'    => [
        'Scheduled conference with teacher.',
        'Picking up student early for medical appointment.',
        'Dropping off medication at health office.',
        'IEP review meeting.',
        'Progress report discussion.',
        'Concerns about classroom behavior.',
        'Volunteering in classroom this morning.',
        'School event — spring concert.',
        'Returning borrowed school materials.',
        null,
    ],
    'vendor'    => [
        'Annual copier maintenance.',
        'Delivering cafeteria supplies.',
        'Network equipment installation.',
        'HVAC inspection.',
        'Fire suppression system check.',
        'Vending machine restock.',
        'Technology refresh — laptop delivery.',
        'Water cooler service.',
        null,
    ],
    'applicant' => [
        'Interview for open teaching position.',
        'Paraprofessional candidate interview.',
        'Bus driver applicant.',
        'Substitute teacher orientation.',
        'Administrative assistant interview.',
        null,
    ],
    'state'     => [
        'State Department of Education site visit.',
        'Title I program review.',
        'Special education compliance audit.',
        'Health and safety inspection.',
        null,
    ],
    'community' => [
        'Attending school board information session.',
        'Boy Scout fundraiser drop-off.',
        'Donation of books for library.',
        'Community garden volunteer.',
        null,
    ],
];

// ─────────────────────────────────────────────────────────────
// 6. Generate 30 days of visits
// ─────────────────────────────────────────────────────────────
$locationId = 1; // Main Office
$insertVisit = $pdo->prepare(
    "INSERT INTO visits
       (organization_id, visitor_id, host_id, reason_id, location_id, status,
        check_in_time, check_out_time, notes, created_at, updated_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

$today   = new DateTime('today');
$nowTs   = time();
$totalVisits = 0;

// School day visit-count distribution by day-of-week (0=Sun … 6=Sat)
$dayWeights = [0=>0, 1=>18, 2=>16, 3=>17, 4=>16, 5=>12, 6=>0];

// Visit time distribution: 07:30–15:30, peak at 08:30 and 14:00
function randomSchoolTime(string $dateStr): string
{
    // Weighted ranges
    $ranges = [
        ['start'=>'07:30','end'=>'08:30','weight'=>15],
        ['start'=>'08:30','end'=>'10:30','weight'=>35],
        ['start'=>'10:30','end'=>'12:00','weight'=>20],
        ['start'=>'12:00','end'=>'13:30','weight'=>10],
        ['start'=>'13:30','end'=>'15:30','weight'=>30],
        ['start'=>'15:30','end'=>'16:30','weight'=>10],
    ];
    $pool = [];
    foreach ($ranges as $r) {
        for ($w = 0; $w < $r['weight']; $w++) $pool[] = $r;
    }
    $pick     = $pool[array_rand($pool)];
    $startMin = strtotime("$dateStr {$pick['start']}");
    $endMin   = strtotime("$dateStr {$pick['end']}");
    $ts       = rand($startMin, $endMin);
    return date('Y-m-d H:i:s', $ts);
}

// Duration in minutes (most visits 10–60 min, occasional 90–180)
function randomDuration(): int
{
    $r = rand(1, 100);
    if ($r <= 40) return rand(10, 25);   // quick visit
    if ($r <= 70) return rand(25, 45);   // normal
    if ($r <= 88) return rand(45, 75);   // meeting
    if ($r <= 96) return rand(75, 120);  // long meeting
    return rand(120, 180);               // very long
}

for ($daysAgo = 30; $daysAgo >= 1; $daysAgo--) {
    $date   = (clone $today)->modify("-$daysAgo days");
    $dow    = (int)$date->format('w');
    $weight = $dayWeights[$dow] ?? 0;
    if ($weight === 0) continue; // skip weekends

    $dateStr  = $date->format('Y-m-d');
    $numVisits = (int)round($weight * (0.7 + lcg_value() * 0.6)); // ±30% variance

    for ($j = 0; $j < $numVisits; $j++) {
        [$visitorId, $vType] = $visitorIds[array_rand($visitorIds)];
        $hostId   = $hostIds[array_rand($hostIds)];
        $reasonId = pickReason($vType, $reasonsByType, $reasonIdSet);
        $notePool = $notesPool[$vType];
        $notes    = $notePool[array_rand($notePool)];

        $checkIn  = randomSchoolTime($dateStr);
        $checkInTs = strtotime($checkIn);

        // Determine status
        $statusRoll = rand(1, 100);
        if ($statusRoll <= 5) {
            $status    = 'cancelled';
            $checkOut  = null;
        } elseif ($statusRoll <= 10) {
            $status    = 'no_show';
            $checkOut  = null;
        } else {
            $status    = 'completed';
            $durMin    = randomDuration();
            $checkOutTs = $checkInTs + ($durMin * 60);
            // Don't let checkout exceed 17:30
            $maxTs = strtotime("$dateStr 17:30:00");
            if ($checkOutTs > $maxTs) $checkOutTs = $maxTs;
            $checkOut = date('Y-m-d H:i:s', $checkOutTs);
        }

        $insertVisit->execute([
            $orgId, $visitorId, $hostId, $reasonId, $locationId,
            $status, $checkIn, $checkOut, $notes, $checkIn, $checkIn,
        ]);
        $totalVisits++;
    }
}

echo "Inserted $totalVisits historical visits.\n";

// ─────────────────────────────────────────────────────────────
// 7. Add today's active check-ins (for Live Logs demo)
// ─────────────────────────────────────────────────────────────
$todayStr = $today->format('Y-m-d');

// Pick 10 visitors to be currently checked in, spread over the last 3 hours
$activeCount  = 10;
$activeVisitors = array_slice($visitorIds, 0, $activeCount); // first 10 (different people)

// Stagger check-in times: 15 min to 3 hours ago
$staggerMins = [175, 148, 121, 97, 84, 63, 51, 38, 24, 12];

foreach ($activeVisitors as $idx => [$visitorId, $vType]) {
    $hostId    = $hostIds[array_rand($hostIds)];
    $reasonId  = pickReason($vType, $reasonsByType, $reasonIdSet);
    $notePool  = $notesPool[$vType];
    $notes     = $notePool[array_rand($notePool)];

    $checkInTs = $nowTs - ($staggerMins[$idx] * 60);
    $checkIn   = date('Y-m-d H:i:s', $checkInTs);

    $insertVisit->execute([
        $orgId, $visitorId, $hostId, $reasonId, $locationId,
        'checked_in', $checkIn, null, $notes, $checkIn, $checkIn,
    ]);
    $totalVisits++;
}
echo "Added $activeCount active check-ins for today.\n";

// Also add a few completed visits earlier today
$earlyToday = [
    ['minsAgo' => 240, 'dur' => 35],
    ['minsAgo' => 210, 'dur' => 20],
    ['minsAgo' => 185, 'dur' => 50],
    ['minsAgo' => 155, 'dur' => 28],
    ['minsAgo' => 130, 'dur' => 45],
];
foreach ($earlyToday as $et) {
    [$visitorId, $vType] = $visitorIds[rand(20, count($visitorIds)-1)];
    $hostId    = $hostIds[array_rand($hostIds)];
    $reasonId  = pickReason($vType, $reasonsByType, $reasonIdSet);
    $checkInTs  = $nowTs - ($et['minsAgo'] * 60);
    $checkOutTs = $checkInTs + ($et['dur'] * 60);
    $checkIn   = date('Y-m-d H:i:s', $checkInTs);
    $checkOut  = date('Y-m-d H:i:s', $checkOutTs);

    $insertVisit->execute([
        $orgId, $visitorId, $hostId, $reasonId, $locationId,
        'completed', $checkIn, $checkOut, null, $checkIn, $checkIn,
    ]);
    $totalVisits++;
}
echo "Added " . count($earlyToday) . " completed visits for today.\n";

// ─────────────────────────────────────────────────────────────
// 8. Summary
// ─────────────────────────────────────────────────────────────
$visitCount    = $pdo->query("SELECT COUNT(*) FROM visits   WHERE organization_id = $orgId")->fetchColumn();
$visitorCount  = $pdo->query("SELECT COUNT(DISTINCT visitor_id) FROM visits WHERE organization_id = $orgId")->fetchColumn();
$hostCount     = $pdo->query("SELECT COUNT(*) FROM hosts    WHERE organization_id = $orgId")->fetchColumn();
$activeNow     = $pdo->query("SELECT COUNT(*) FROM visits   WHERE organization_id = $orgId AND status = 'checked_in'")->fetchColumn();

echo "\n========================================\n";
echo "Demo data seeding complete!\n";
echo "  Hosts (from AD):   $hostCount\n";
echo "  Visitors:          $visitorCount\n";
echo "  Total visits:      $visitCount\n";
echo "  Active right now:  $activeNow  ← visible in Live Logs\n";
echo "========================================\n";
