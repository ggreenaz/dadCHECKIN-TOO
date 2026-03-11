<?php
$stepFile = __DIR__ . '/guided/' . ($currentStep ?? 'organization') . '.php';
if (file_exists($stepFile)) {
    include $stepFile;
} else {
    echo '<div class="card"><p>Unknown step.</p></div>';
}
