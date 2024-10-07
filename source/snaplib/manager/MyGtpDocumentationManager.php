<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\manager;

use Snap\api\exception\DocumentContentEmpty;
use Snap\api\exception\DocumentNotFound;
use Snap\object\MyDocumentation;
use Snap\object\MyLocalizedContent;
use Snap\TLogging;

/**
 * This class contains methods related to the authentication process
 *
 * @author Cheok Jia Fuei <cheok@silverstream.my>
 * @version 1.0
 * @created 07-Oct-2020
 */
class MyGtpDocumentationManager
{
    use TLogging;

    /** @var Snap\App $app */
    private $app = null;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Get the document content for the language specified or default to English
     *
     * @param  string $code
     * @param  string $language
     * @return MyDocumentation
     */
    public function getDocumentForLanguage($code, $language, $partner)
    {
        if (!in_array($language, [MyLocalizedContent::LANG_BAHASA, MyLocalizedContent::LANG_ENGLISH, MyLocalizedContent::LANG_CHINESE])) {
            $language = MyLocalizedContent::LANG_ENGLISH;
        }

        /** @var  MyDocumentation **/
        //$document = $this->app->mydocumentationStore()->getByField('code', $code);
        $document = $this->app->mydocumentationStore()
                    ->searchTable()->select()
                    ->where('code', $code)
                    ->andWhere('partnerid', $partner->id)
                    ->one();

        if (!$document) {
            $this->log(__METHOD__ . '() Unable to get document for code (' . $code . ')', SNAP_LOG_ERROR);
            throw DocumentNotFound::fromTransaction([], ['code' => $code]);
        }

        // Select content for language
        $this->log(__METHOD__ . '() Document found ' . $document->name . ' for code (' . $code . ')', SNAP_LOG_DEBUG);
        $document->language = $language;

        if (!$document->filecontent) {
            $this->log(__METHOD__ . '()  Document (' . $code . ') content is empty for language (' . $language . ')', SNAP_LOG_ERROR);
            throw DocumentContentEmpty::fromTransaction([], ['code' => $code, 'language' => $language]);
        }

        return $document;
    }
}
