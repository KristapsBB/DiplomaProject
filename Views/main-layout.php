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
        <title><?php echo $this->getPageParam('title') . ' — diploma-project'; ?></title>
        <?php $this->head(); ?>
    </head>
    <body class="<?php $this->printBodyClass(); ?>">
        <header class="page__header"></header>
        <div class="page__container">
            <?php $this->theRootView(); ?>
        </div>
        <?php $this->footer(); ?>
    </body>
</html>
