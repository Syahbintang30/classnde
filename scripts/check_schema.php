<?php
$db = new PDO('sqlite:' . __DIR__ . '/../database/database.sqlite');
$rows = $db->query("PRAGMA table_info('coaching_bookings')")->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);
