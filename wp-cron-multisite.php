<?php
// Load WP
require_once( dirname( __FILE__ ) . '/wpsite/wp-load.php' );

add_log( "Starting cron execution" );

// Check Version
global $wp_version;
$gt_4_6 = version_compare( $wp_version, '4.6.0', '>=' );

// Get Blogs
$args  = array( 'archived' => 0, 'deleted' => 0, 'public' => 1 );
$blogs = $gt_4_6 ? get_sites( $args ) : @wp_get_sites( $args ); // >= 4.6

// Run Cron on each blog
echo "Running Crons: " . PHP_EOL;
$agent = 'WordPress/' . $wp_version . '; ' . home_url();
// $time  = time();

$networkUrl = network_site_url();
$command = $networkUrl . 'wpsite/wp-cron.php?doing_wp_cron'; // =' . $time . '&ver=' . $wp_version;
$ch = curl_init( $command );
$rc = curl_setopt( $ch, CURLOPT_RETURNTRANSFER, false );
$rc = curl_exec( $ch );
curl_close( $ch );

print_r( $rc );
print_r( "\t OK " . $command . PHP_EOL );
add_log( $command . "\t" . $rc );

foreach ( $blogs as $blog ) {
    $domain  = $gt_4_6 ? $blog->domain : $blog['domain'];
    $path    = $gt_4_6 ? $blog->path : $blog['path'];
    $command = "http://" . $domain . ( $path ? $path : '/' ) . 'wpsite/wp-cron.php?doing_wp_cron'; // =' . $time . '&ver=' . $wp_version;

    $ch = curl_init( $command );
    $rc = curl_setopt( $ch, CURLOPT_RETURNTRANSFER, false );
    $rc = curl_exec( $ch );
    curl_close( $ch );

    print_r( $rc );
    print_r( "\t OK " . $command . PHP_EOL );
    add_log( $command . "\t" . $rc );
}

add_log( "Cron execution finished" );

function add_log( $strText ) {
  $strLogText = date('Y-m-d H:i:s') . " " . $strText;
  $strDebugFile = __DIR__.'/wp-cron-multisite.kklog';
  file_put_contents($strDebugFile, $strLogText."\n", FILE_APPEND | LOCK_EX);
}