<?php
include 'api/lib/Signup.class.php';


$token = $_GET['token'];
try {
    if (Signup::verify_account($token)) {
        ?>
<h1 style="color: green">Verified</h1>
<?php
    } else {
        ?>
<h1 style="color: red">Cannot Verify</h1>
<?php
    }
} catch (Exception $e) {
    ?>
<h1 style="color: orange">Already Verified</h1>
<h1 style="color: orange"><?=$e->getMessage()?>
</h1>
<?php
}
