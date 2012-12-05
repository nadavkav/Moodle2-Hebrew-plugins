<?php // $Id: view.php,v 1.16 2009-06-05 20:12:38 jfilip Exp $

/**
 * This page prints a particular instance of Blackboard Collaborate.
 *
 * @version $Id: view.php,v 1.16 2009-06-05 20:12:38 jfilip Exp $
 * @author Justin Filip <jfilip@remote-learner.net>
 * @author Remote Learner - http://www.remote-learner.net/
 */


    require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
    require_once dirname(__FILE__) . '/lib.php';

	echo('Forcing Blackboard Collaborate cron job to run...');
	elluminate_cron();

