
<?php
session_start(); // Start the session

// Default theme if no theme is selected
$defaultTheme = 'style'; // Change this to your desired default theme

// Get the selected theme from the session or use the default theme
$selectedTheme = isset($_SESSION['selected_theme']) ? $_SESSION['selected_theme'] : $defaultTheme;

// Define an array of allowed themes to prevent vulnerabilities


$allowedThemes = [
    'style' => 'style.css',
    'darkmode' => 'darkmode.style.css',
    'lightmode' => 'lightmode.style.css',
    'ltgreen' => 'ltgreen.style.css',
    'olive' => 'olive.style.css',
    'raspberry' => 'raspberry.style.css',
    'trc' => 'trc.style.css',
    'blueshades' => 'blueshades.style.css',
    'gator' => 'gator.style.css',
    'packers' => 'packers.style.css',
    'royalblue' => 'royalblue.style.css',
    'teal' => 'teal.style.css',
    'red' => 'red.style.css',
    'limegreen' => 'limegreen.style.css',
    'majorblue' => 'majorblue.style.css',
    'yellow-charcoal' => 'yellow-charcoal.style.css',
    'academi' => 'academi.style.css',
    // Add any additional themes here
];

// Ensure the selected theme is valid; otherwise, fallback to default
if (!array_key_exists($selectedTheme, $allowedThemes)) {
    $selectedTheme = $defaultTheme;
}

// Generate the CSS file path based on the selected theme
$cssFileName = $allowedThemes[$selectedTheme];
$cssFilePath = '../css/' . $cssFileName;

// Set the Content-Type header to indicate CSS
header('Content-Type: text/css');

// Output the CSS file content
readfile($cssFilePath);
?>
