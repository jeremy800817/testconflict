//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Ext.define('snap.store.EventProcessorType', {
    extend: 'Ext.data.ArrayStore',
    // autoLoad: true,

    model: 'snap.model.EventProcessorType',
    alias: 'store.EventProcessorType',
    data: [
        [ '\\Snap\\object\\EmailEventProcessor', 'Email', 'Email'],
        [ '\\Snap\\object\\SmsEventProcessor', 'Sms', 'Sms'],
        ['\\Snap\\object\\TelegramEventProcessor', 'Telegram', 'Telegram'],
        ['\\Snap\\object\\MyGtpEmailEventProcessor', 'MyGtpEmail', 'MyGtpEmail'],
        ['\\Snap\\object\\MyGtpPushEventProcessor', 'MyGtpPush', 'MyGtpPush'],
        ['\\Snap\\object\\MyGtpSmsEventProcessor', 'MyGtpSms', 'MyGtpSms']

    ]

});
