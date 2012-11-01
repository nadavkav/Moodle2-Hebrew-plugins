<?php

$hassidepre = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-pre', $OUTPUT));
$hassidepost = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-post', $OUTPUT));
$showsidepre = $hassidepre && !$PAGE->blocks->region_completely_docked('side-pre', $OUTPUT);
$showsidepost = $hassidepost && !$PAGE->blocks->region_completely_docked('side-post', $OUTPUT);

$custommenu = $OUTPUT->custom_menu();
$hascustommenu = (empty($PAGE->layout_options['nocustommenu']) && !empty($custommenu));

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
    <meta name="description" content="<?php p(strip_tags(format_text($SITE->summary, FORMAT_HTML))) ?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
</head>
<body id="<?php p($PAGE->bodyid) ?>" class="<?php p($PAGE->bodyclasses.' '.join(' ', $bodyclasses)) ?>">
<?php echo $OUTPUT->standard_top_of_body_html() ?>

<div id="page">
    <div id="page-top">

        <div id="page-header">
        <div class="services">
            <?php if (!empty($PAGE->theme->settings->service_home)) echo "<a href=\"{$PAGE->theme->settings->service_home}\" class=\"home\"></a>"; ?>
            <?php if (!empty($PAGE->theme->settings->service_search)) echo "<a href=\"{$PAGE->theme->settings->service_search}\" class=\"search\"></a>"; ?>
            <?php if (!empty($PAGE->theme->settings->service_about)) echo "<a href=\"{$PAGE->theme->settings->service_about}\" class=\"about\"></a>"; ?>
            <?php if (!empty($PAGE->theme->settings->service_rss)) echo "<a href=\"{$PAGE->theme->settings->service_rss}\" class=\"rss\"></a>"; ?>
            <?php if (!empty($PAGE->theme->settings->service_help)) echo "<a href=\"{$PAGE->theme->settings->service_help}\" class=\"help\"></a>"; ?>
            <?php if (!empty($PAGE->theme->settings->service_email)) echo "<a href=\"{$PAGE->theme->settings->service_email}\" class=\"email\"></a>"; ?>
            <?php if (!empty($PAGE->theme->settings->service_support)) echo "<a href=\"{$PAGE->theme->settings->service_support}\" class=\"support\"></a>"; ?>
        </div>
        <div class="collegelogo"></div>
        <h1 class="headermain"><?php echo $PAGE->heading ?></h1>
        <div class="headermenu"><?php
            if ($USER->id > 1) echo $OUTPUT->user_picture($USER);
            echo $OUTPUT->login_info();
            echo $OUTPUT->lang_menu();
            echo $PAGE->headingmenu;
        ?></div>
        <?php if ($hascustommenu) { ?>
        <div id="custommenu"><?php echo $custommenu; ?></div>
         <?php } ?>
    </div>
        <!-- END OF HEADER -->

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

                                    <!-- Main Text -->
                                    <div class="box drop" style="display: block">
                                        <h3>הנחיות לקורסים של מכללה ברירת מחדל</h3>
                                        <table class="text">
                                            <tr>
                                                <td>
                                                    Lacus aenean sed aliquam, platea magnis sociis ridiculus. Sit! Nunc, lectus elementum? Lacus amet mus sit, platea tincidunt! Quis penatibus, porta rhoncus dapibus ut dis, magnis diam odio, ac etiam? Dis vel, tincidunt elit cursus cursus sed est duis. Amet, nec aenean, phasellus nascetur, facilisis?<br><br>Ridiculus dignissim, turpis auctor arcu porta dapibus mid, magnis adipiscing, pid cursus urna? Sit magna, duis eros montes magnis massa elit mus?
                                                </td>
                                                <td class="props">
                                                    <div>
                                                        <strong>מנהלת המכללה - שרית כהן</strong><br>
                                                        <a href="mailto:saritco@orot.ac.il" target="_blank" class="email">saritco@orot.ac.il</a>
                                                        <div style="clear: both;height:10px"></div>
                                                        קורסים זמינים: <strong>175</strong><br />
                                                        משתמשים: <strong>869</strong><br />
                                                        משתמשים פעילים: <strong>249</strong><br />
                                                        מרצים: <strong>65</strong><br />
                                                        עדכון אחרון: <strong>31/8/2012</strong><br />
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>

                                    <!-- Course List Simple -->
                                    <br><br>
                                    <div style="display: block" class="heading">קורסים זמינים</div>
                                    <div class="box" style="display: block">
                                        <table class="text course">
                                            <tr>
                                                <td>
                                                    <a class="title" title="שם הקורס" href="#">שם הקורס</a>
                                                    <div class="teacher"> מנחה - <a href="#">שם המורה</a></div>
                                                    <div class="teacher"> מנחה - <a href="#">שם המורה</a></div>
                                                </td>
                                                <td class="props">
                                                    <div>
                                                        <strong>תקופת הקורס:</strong> הקורס יתקיים במשך <strong>21</strong> ימים.
                                                        <br />21.04.2013
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <a class="title" title="שם הקורס" href="#">שם הקורס</a>
                                                    <div class="teacher"> מנחה - <a href="#">שם המורה</a></div>
                                                    <div class="teacher"> מנחה - <a href="#">שם המורה</a></div>
                                                </td>
                                                <td class="props">
                                                    <div>
                                                        <strong>תקופת הקורס:</strong> הקורס יתקיים במשך <strong>21</strong> ימים.
                                                        <br />21.04.2013
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <a class="title" title="שם הקורס" href="#">שם הקורס</a>
                                                    <div class="teacher"> מנחה - <a href="#">שם המורה</a></div>
                                                    <div class="teacher"> מנחה - <a href="#">שם המורה</a></div>
                                                </td>
                                                <td class="props">
                                                    <div>
                                                        <strong>תקופת הקורס:</strong> הקורס יתקיים במשך <strong>21</strong> ימים.
                                                        <br />21.04.2013
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <a class="title" title="שם הקורס" href="#">שם הקורס</a>
                                                    <div class="teacher"> מנחה - <a href="#">שם המורה</a></div>
                                                    <div class="teacher"> מנחה - <a href="#">שם המורה</a></div>
                                                </td>
                                                <td class="props">
                                                    <div>
                                                        <strong>תקופת הקורס:</strong> הקורס יתקיים במשך <strong>21</strong> ימים.
                                                        <br />21.04.2013
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>

                                    <!-- Training Boxes -->
                                    <br><br>
                                    <div class="box drop near" style="width:48%;display: block">
                                        <h3>הדרכה למרצים</h3>
                                        <div class="text train teacher">
                                            המחשב המקובל אחרונים ב שמו. אחד על המזנון ייִדיש, בישול ואמנות תיאטרון אל שתי, או שדרות הגולשות המקושרים אחד.
                                            <a href="#" class="button">כניסה</a>
                                        </div>
                                    </div>
                                    <div class="box drop far" style="width:48%;display: block">
                                        <h3>הדרכה לסטודנטים</h3>
                                        <div class="text train student">
                                            ציור הספרות כתב או, שינויים מדויקים וספציפיים את זאת, כלים רפואה צ'ט את. תרבות בישול זאת את, כלל מה לימודים אתנולוגיה, על כלים בקרבת מיזמים לוח.
                                            <a href="#" class="button">כניסה</a>
                                        </div>
                                    </div>
                                    <div style="clear: both"></div>

                                    <!-- Generic Box -->
                                    <br><br>
                                    <div class="box" style="display: block">
                                        <h3>מה חדש בקורסים שלי</h3>
                                        <table class="text">
                                            <tr>
                                                <td>
                                                    Lacus aenean sed aliquam, platea magnis sociis ridiculus. Sit! Nunc, lectus elementum? Lacus amet mus sit, platea tincidunt! Quis penatibus, porta rhoncus dapibus ut dis, magnis diam odio, ac etiam? Dis vel, tincidunt elit cursus cursus sed est duis. Amet, nec aenean, phasellus nascetur, facilisis?<br><br>Ridiculus dignissim, turpis auctor arcu porta dapibus mid, magnis adipiscing, pid cursus urna? Sit magna, duis eros montes magnis massa elit mus?
                                                </td>
                                                <td class="props">
                                                    <div>
                                                        קורסים זמינים: <strong>175</strong><br />
                                                        משתמשים: <strong>869</strong><br />
                                                        משתמשים פעילים: <strong>249</strong><br />
                                                        מרצים: <strong>65</strong><br />
                                                        עדכון אחרון: <strong>31/8/2012</strong><br />
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>

                                    <!-- Small Boxes -->
                                    <br><br>
                                    <div class="smallboxes" style="display: block">
                                        <a href="#" class="box drop">קורסים תשע"ג</a>
                                        <a href="#" class="box drop">קורסים תשע"ב</a>
                                        <a href="#" class="box drop">קורסים תשע"א</a>
                                        <a href="#" class="box drop">קורסים <span class="important">שלי</span> תשע"ג</a>
                                        <a href="#" class="box drop">כל הקורסים <span class="important">שלי</span></a>
                                    </div>
                                    <div style="clear: both"></div>

                                    <!-- Course List Simple -->
                                    <br><br>
                                    <div class="box" style="display: block">
                                        <h3>קורסים</h3>
                                        <table class="text courses">
                                            <tr>
                                                <td>
                                                    <div class="list">
                                                        <a class="title" href="#">Course number 1</a>
                                                        <a class="title" href="#">Course number 2</a>
                                                        <a class="title" href="#">Course number 3</a>
                                                        <a class="title" href="#">Course number 4</a>
                                                    </div>
                                                    <div class="paging">
                                                        <label>Page</label>
                                                        <a class="first dis" href="#"></a>
                                                        <a class="prev" href="#"></a>
                                                        <a href="#">1</a>
                                                        <a href="#">2</a>
                                                        <a href="#">3</a>
                                                        <a href="#">4</a>
                                                        <a class="sel" href="#">5</a>
                                                        <a href="#">6</a>
                                                        <a href="#">7</a>
                                                        <a href="#">8</a>
                                                        <a href="#">9</a>
                                                        <a href="#">10</a>
                                                        <a class="next" href="#"></a>
                                                        <a class="last" href="#"></a>
                                                    </div>
                                                </td>
                                                <td class="props">
                                                    <div>
                                                        <label>קטגוריות קורסים</label>
                                                        <select>
                                                            <option>תשע"ג 2013</option>
                                                            <option>תשע"ב 2012</option>
                                                            <option>תשע"א 2011</option>
                                                            <option>תש"ע 2010</option>
                                                        </select>
                                                        <br><br><br>
                                                        <a href="#" class="button">מיון קורסים על פי שם</a>
                                                        <a href="#" class="button">הוספת קורס חדש</a>
                                                        <div style="clear: both"></div>
                                                        <br><br>
                                                        <label>חפש קורסים לפי שם</label>
                                                        <input type="text" />
                                                        <input style="width:auto;margin-top:6px" class="button" type="submit" value="הצג" />
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>

                                    <!-- Default content  -->
                                    <br><br>
                                    <div id="defaultContent" class="theme1" style="display: block">
                                        <?php echo $OUTPUT->main_content() ?>
                                    </div>

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
    <!-- START OF FOOTER -->
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
            <a class="college" href="http://www.orot.ac.il/" target="_blank"></a>
            <a class="mofet" href="http://www.mofet.macam.ac.il/" target="_blank"></a>
            <a class="macam" href="http://www.macam.ac.il/" target="_blank"></a>
        </div>
    </div>
</div>
<?php echo $OUTPUT->standard_footer_html(); // display performance and developer debugging info, if enabled ?>
<?php echo $OUTPUT->standard_end_of_body_html() ?>
</body>
</html>