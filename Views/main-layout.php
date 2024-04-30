<?php

/**
 * @var DiplomaProject\Core\Modules\Viewer $this
 */
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $this->page_params['title'] . ' — diploma-project'; ?></title>
    </head>
    <body>
        <?php $this->theRootView(); ?>
    </body>
</html>
