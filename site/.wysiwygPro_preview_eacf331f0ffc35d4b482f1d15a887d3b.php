<?php
if ($_GET['randomId'] != "7lkK7MboWN4ssPQpu8fUuYA8LkwY_Zmq3O2r6xlxhq6RBPt7540MmmhUrVk3a_AA") {
    echo "Access Denied";
    exit();
}

// display the HTML code:
echo stripslashes($_POST['wproPreviewHTML']);

?>  
