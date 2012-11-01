<?php

class theme_avalon_core_renderer extends core_renderer {

    /**
     * Renders a custom menu object (located in outputcomponents.php)
     *
     * The custom menu this method override the render_custom_menu function
     * in outputrenderers.php
     * @staticvar int $menucount
     * @param custom_menu $menu
     * @return string
     */
   /* protected function render_custom_menu(custom_menu $menu) {

        if (!right_to_left()) { // Keep YUI3 navmenu for LTR UI
            parent::render_custom_menu($menu);
        }

        // If the menu has no children return an empty string
        if (!$menu->has_children()) {
            return '';
        }

        // Add a login or logout link
        if (isloggedin()) {
            $branchlabel = get_string('logout');
            $branchurl   = new moodle_url('/login/logout.php');
        } else {
            $branchlabel = get_string('login');
            $branchurl   = new moodle_url('/login/index.php');
        }
        $branch = $menu->add($branchlabel, $branchurl, $branchlabel, -1);

        // Initialise this custom menu
        $content = html_writer::start_tag('ul', array('class'=>'dropdown dropdown-horizontal'));
        // Render each child
        foreach ($menu->get_children() as $item) {
            $content .= $this->render_custom_menu_item($item);
        }
        // Close the open tags
        $content .= html_writer::end_tag('ul');
        // Return the custom menu
        return $content;
    }*/

    /**
     * Renders a custom menu node as part of a submenu
     *
     * The custom menu this method override the render_custom_menu_item function
     * in outputrenderers.php
     *
     * @see render_custom_menu()
     *
     * @staticvar int $submenucount
     * @param custom_menu_item $menunode
     * @return string
     */
  /* protected function render_custom_menu_item(custom_menu_item $menunode) {

        if (!right_to_left()) { // Keep YUI3 navmenu for LTR UI
            parent::render_custom_menu_item($menunode);
        }

        // Required to ensure we get unique trackable id's
        static $submenucount = 0;
        $content = html_writer::start_tag('li');
        if ($menunode->has_children()) {
            // If the child has menus render it as a sub menu
            $submenucount++;
            if ($menunode->get_url() !== null) {
                $url = $menunode->get_url();
            } else {
                $url = '#cm_submenu_'.$submenucount;
            }
            $content .= html_writer::start_tag('span', array('class'=>'customitem'));
            $content .= html_writer::link($url, $menunode->get_text(), array('title'=>$menunode->get_title()));
            $content .= html_writer::end_tag('span');
            $content .= html_writer::start_tag('ul');
            foreach ($menunode->get_children() as $menunode) {
                $content .= $this->render_custom_menu_item($menunode);
            }
            $content .= html_writer::end_tag('ul');
        } else {
            // The node doesn't have children so produce a final menuitem

            if ($menunode->get_url() !== null) {
                $url = $menunode->get_url();
            } else {
                $url = '#';
            }
            $content .= html_writer::link($url, $menunode->get_text(), array('title'=>$menunode->get_title()));
        }
        $content .= html_writer::end_tag('li');
        // Return the sub menu
        return $content;
    }      */

    /***
     *  This renderers a custom menu in the page footer
     * @param string The custom menu string. If emtpy loads this from theme's config
     * @return string  HTML rendered menu
     */
    public function footer_custom_menu($custommenuitems = ''){

        global $PAGE;

        //Get custom menu string
        if (empty($custommenuitems) && !empty($PAGE->theme->settings->collegefooter)) {
            //Get menu string from config
            $custommenuitems = $PAGE->theme->settings->collegefooter;
        }
        else if (!empty($custommenuitems)){
            return '';
        }

        //Parse string into custom_menu object
        $custommenu = new custom_menu($custommenuitems, current_language());

        //Render HTML
        $content = $this->render_footer_custom_menu($custommenu);

        return $content;
    }


    protected function render_footer_custom_menu($menu){

        // If the menu has no children return an empty string
        if (!$menu->has_children()) {
            return '';
        }

        $content = '';

        // Render each child
        foreach ($menu->get_children() as $item) {
            $content .= $this->render_footer_custom_menu_item($item);
        }

        // Return the custom menu
        return $content;
    }

    protected function render_footer_custom_menu_item(custom_menu_item $menunode){

        //Render column start tag
        $content = html_writer::start_tag('div',array('class' => 'section'));

        //Render column  headline
        $content .= html_writer::start_tag('h3');
        if ($menunode->get_url() !== null) {
            $content .= html_writer::link($menunode->get_url(), $menunode->get_text(), array('title'=>$menunode->get_title(), 'target' => '_blank'));
        }
        else {
            $content .=  $menunode->get_text();
        }

        //Render column headline end tag
        $content .= html_writer::end_tag('h3');

        //Render columns children
        foreach ($menunode->get_children() as $subnode){

            //For each child render a link (or an empty link)
            if ($subnode->get_url() !== null) {
                $content .= html_writer::link($subnode->get_url(), $subnode->get_text(), array('title'=>$subnode->get_title(), 'target' => '_blank'));
            }
            else {
                $content .= html_writer::link('', $subnode->get_text(), array('title'=>$subnode->get_title()));
            }

        }

        //Render column end tag
        $content .=  html_writer::end_tag('div');

        return $content;

    }


    protected function render_course_teachers()
    {
        global $CFG, $COURSE, $DB, $OUTPUT;

        $context = get_context_instance(CONTEXT_COURSE, $COURSE->id);
        /// first find all roles that are supposed to be displayed
        if (!empty($CFG->coursecontact)) {
            $managerroles = explode(',', $CFG->coursecontact);
            $namesarray = array();
            $rusers = array();

            if (!isset($COURSE->managers)) {
                $rusers = get_role_users($managerroles, $context, true,
                    'ra.id AS raid, u.id, u.username, u.firstname, u.lastname, u.aim,
                 r.name AS rolename, r.sortorder, r.id AS roleid',
                    'r.sortorder ASC, u.lastname ASC');
            } else {
                //  use the managers array if we have it for perf reasosn
                //  populate the datastructure like output of get_role_users();
                foreach ($COURSE->managers as $manager) {
                    $u = new stdClass();
                    $u = $manager->user;
                    $u->roleid = $manager->roleid;
                    $u->rolename = $manager->rolename;

                    $rusers[] = $u;
                }
            }

            /// Rename some of the role names if needed
            if (isset($context)) {
                $aliasnames = $DB->get_records('role_names', array('contextid' => $context->id), '', 'roleid,contextid,name');
            }

            $namesarray = array();
            $canviewfullnames = has_capability('moodle/site:viewfullnames', $context);
            foreach ($rusers as $ra) {
                if (isset($namesarray[$ra->id])) {
                    //  only display a user once with the higest sortorder role
                    continue;
                }

                if (isset($aliasnames[$ra->roleid])) {
                    $ra->rolename = $aliasnames[$ra->roleid]->name;
                }

                $fullname = '';
                if (!empty($ra->aim)) {
                    $fullname .= $ra->aim.' ';
                }
                $fullname .= fullname($ra, $canviewfullnames);
                //$namesarray[$ra->id] = format_string($ra->rolename).': '.
                $namesarray[$ra->id] = html_writer::link(new moodle_url('/user/view.php', array('id' => $ra->id, 'course' => SITEID)), $fullname);
            }

            if (!empty($namesarray)) {
                if (count($namesarray) > 1) {
                    $teacherslabel = get_string('teachers', 'theme_avalon');
                } else {
                    $teacherslabel = get_string('teacher', 'theme_avalon');
                }
//            echo html_writer::start_tag('ul', array('class'=>'teachers'));
//            foreach ($namesarray as $name) {
//                echo html_writer::tag('li', $name);
//            }
//            echo html_writer::end_tag('ul');
                echo html_writer::start_tag('div', array('class' => 'teachers'));
                $teacherlist = $teacherslabel . ': ';
                foreach ($namesarray as $name) {
                    $teacherlist .= "$name,";
                }
                echo rtrim($teacherlist, ',');
                echo html_writer::end_tag('div');
            }
        }
    }


}