<?php
echo "Step 1: Starting...<br>";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
    echo "Step 2: Session started<br>";
} else {
    echo "Step 2: Session already started<br>";
}

echo "Step 3: Testing complete!";
?>