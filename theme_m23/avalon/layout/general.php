<?php

$hasheading = ($PAGE->heading);
$hasnavbar = (empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar());
$hasfooter = (empty($PAGE->layout_options['nofooter']));
$hassidepre = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-pre', $OUTPUT));
$hassidepost = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-post', $OUTPUT));
$haslogininfo = (empty($PAGE->layout_options['nologininfo']));

$showsidepre = ($hassidepre && !$PAGE->blocks->region_completely_docked('side-pre', $OUTPUT));
$showsidepost = ($hassidepost && !$PAGE->blocks->region_completely_docked('side-post', $OUTPUT));

$custommenu = $OUTPUT->custom_menu();
$hascustommenu = (empty($PAGE->layout_options['nocustommenu']));// && !empty($custommenu));

$bodyclasses = array();
if ($showsidepre && !$showsidepost) {
    if (!right_to_left()) {
        $bodyclasses[] = 'side-pre-only';
    }else{
        $bodyclasses[] = 'side-post-only';
    }
} else if ($showsidepost && !$showsidepre) {
    if (!right_to_left()) {
        $bodyclasses[] = 'side-post-only';
    }else{
        $bodyclasses[] = 'side-pre-only';
    }
} else if (!$showsidepost && !$showsidepre) {
    $bodyclasses[] = 'content-only';
}
if ($hascustommenu) {
    $bodyclasses[] = 'has_custom_menu';
}

echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes() ?>>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $PAGE->title ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
</head>
<body id="<?php p($PAGE->bodyid) ?>" class="<?php p($PAGE->bodyclasses.' '.join(' ', $bodyclasses)) ?>">
<?php echo $OUTPUT->standard_top_of_body_html() ?>
<div id="page">
    <div id="page-top">

        <?php if ($hasheading || $hasnavbar) { ?>
        <div id="page-header">
            <div class="services">
                <?php if (!empty($PAGE->theme->settings->service_home)) echo "<a href=\"{$PAGE->theme->settings->service_home}\" class=\"home\" target=\"_blank\"></a>"; ?>
                <?php if (!empty($PAGE->theme->settings->service_search)) echo "<a href=\"{$PAGE->theme->settings->service_search}\" class=\"search\" target=\"_blank\"></a>"; ?>
                <?php if (!empty($PAGE->theme->settings->service_about)) echo "<a href=\"{$PAGE->theme->settings->service_about}\" class=\"about\" target=\"_blank\"></a>"; ?>
                <?php if (!empty($PAGE->theme->settings->service_rss)) echo "<a href=\"{$PAGE->theme->settings->service_rss}\" class=\"rss\" target=\"_blank\"></a>"; ?>
                <?php if (!empty($PAGE->theme->settings->service_help)) echo "<a href=\"{$PAGE->theme->settings->service_help}\" class=\"help\" target=\"_blank\"></a>"; ?>
                <?php if (!empty($PAGE->theme->settings->service_email)) echo "<a href=\"{$PAGE->theme->settings->service_email}\" class=\"email\" target=\"_blank\"></a>"; ?>
                <?php if (!empty($PAGE->theme->settings->service_support)) echo "<a href=\"{$PAGE->theme->settings->service_support}\" class=\"support\" target=\"_blank\"></a>"; ?>
            </div>
            <div class="collegelogo"></div>
            <?php if ($hasheading) { ?>
            <h1 class="headermain" title="<?php echo $PAGE->heading ?>">
                <?php echo $PAGE->heading ?>
            </h1>

            <div class="headermenu">
                <?php if ($USER->id > 1) echo $OUTPUT->user_picture($USER) ?>
                <?php
                if ($haslogininfo) { echo $OUTPUT->login_info(); }
                if (!empty($PAGE->layout_options['langmenu'])) { echo $OUTPUT->lang_menu(); }
                ?>
            </div>
            <?php } ?>
            <div class="teachername"><?php $OUTPUT->render_course_teachers() ?></div>
        <?php if ($hascustommenu) { ?>
            <div id="custommenu">
                <?php echo $custommenu; ?>
                <div class="navbutton"> <?php echo $PAGE->button; ?></div>
            </div>
        <?php } ?>
        <?php if ($hasnavbar) { ?>
            <div class="navbar clearfix">
                <div class="breadcrumb"><?php echo $OUTPUT->navbar(); ?></div>
                <div class="info"><?php echo page_doc_link(get_string('moodledocslink')) ?></div>
            </div>
        <?php } ?>
        </div>
        <?php } ?>

        <div id="page-middle">
            <div id="page-content">
                <?php
                if(trim($PAGE->theme->settings->collegenotice) !== '')
                {
                    echo '<div class="collegenotice"><div class="heading">'.get_string('importantnotice','theme_avalon').'</div><div class="content">';
                    echo clean_text(trim($PAGE->theme->settings->collegenotice));
                    echo '</div></div>';
                }
                ?>
                <div id="region-main-box">
                    <div id="region-post-box">

                        <div id="region-main-wrap">
                            <div id="region-main">
                                <div class="region-content">
                                    <?php if ($hasheading) { ?>
                                        <div title="<?php echo $PAGE->heading ?>" class="heading"><?php echo $PAGE->heading ?></div>
                                    <?php } ?>
                                    <?php echo $OUTPUT->main_content() ?>
                                </div>
                            </div>
                        </div>

                        <?php if ($hassidepre OR (right_to_left() AND $hassidepost)) { ?>
                        <div id="region-pre" class="block-region">
                            <div class="region-content">
                                <?php
                                if (!right_to_left()) {
                                    echo $OUTPUT->blocks_for_region('side-pre');
                                } elseif ($hassidepost) {
                                    echo $OUTPUT->blocks_for_region('side-post');
                                } ?>

                            </div>
                        </div>
                        <?php } ?>

                        <?php if ($hassidepost OR (right_to_left() AND $hassidepre)) { ?>
                        <div id="region-post" class="block-region">
                            <div class="region-content">
                                <?php
                                if (!right_to_left()) {
                                    echo $OUTPUT->blocks_for_region('side-post');
                                } elseif ($hassidepre) {
                                    echo $OUTPUT->blocks_for_region('side-pre');
                                } ?>
                            </div>
                        </div>
                        <?php } ?>

                    </div>
                </div>
            </div>
        </div>

    </div>

    <?php if ($hasfooter) { ?>
    <div id="page-footer">
        <div class="bottomnear">
            <?php if ($USER->id > 1) echo $OUTPUT->user_picture($USER) ?>
            <?php echo $OUTPUT->login_info(); ?>
            <div class="collegename"><?php echo $PAGE->theme->settings->collegeabout ?></div>
            <div class="collegelinks">
                <?php echo $OUTPUT->footer_custom_menu();?>
            </div>
        </div>
        <div class="bottomfar">
            <a class="college" <?php if (!empty($PAGE->theme->settings->service_home)) echo "href=\"{$PAGE->theme->settings->service_home}\""; ?> target="_blank"></a>
            <a class="mofet" href="http://www.mofet.macam.ac.il/" target="_blank"></a>
            <a class="macam" href="http://www.macam.ac.il/" target="_blank"></a>
        </div>
    </div>
    <?php } ?>

</div>
<?php echo $OUTPUT->standard_footer_html(); // display performance and developer debugging info, if enabled ?>
<?php echo $OUTPUT->standard_end_of_body_html() ?>
</body>
</html>