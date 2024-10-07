<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
namespace Snap\handler;

use Snap\App;
use Snap\object\MyAnnouncementTheme;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author  Azam <azam@silverstream.my>
 * @version 1.0
 */
class myannouncementthemehandler extends CompositeHandler
{
    protected $app;

    function __construct(App $app)
    {
        parent::__construct('/root/system', 'myannouncementtheme');

        $this->mapActionToRights('list', 'list');
        $this->mapActionToRights('fillform', 'add');
        $this->mapActionToRights('fillform', 'edit');
        $this->mapActionToRights('detailview', 'list');

        $this->app = $app;

        $announcementthemeStore = $app->myannouncementthemeStore();
        $this->addChild(new ext6gridhandler($this, $announcementthemeStore, 1));
    }

    /**
     * Function to masssage data before listing
     *
     * @param  MyAnnouncementTheme[] $objects
     * @param  array $params
     * @param  array $records
     * @return void
     */
    function onPreListing($objects, $params, $records)
    {
        array_walk($records, function (&$record, $key) {
            if (100 < strlen($record['template'])) {
                $record['shorten_template'] = substr(htmlspecialchars($record['template']), 0, 100) . '...';
            } else {
                $record['shorten_template'] = htmlspecialchars($record['template']);
            }
        });

        return $records;
    }

    /**
     * function to populate selection data into form
     **/
    function fillform($app, $params)
    {
        echo json_encode([
            'success' => true,
        ]);
    }
}
