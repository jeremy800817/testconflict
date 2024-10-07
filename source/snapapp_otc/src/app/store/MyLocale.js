//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Ext.define('snap.store.MyLocale', {
  extend: 'snap.store.Base',
  fields: ['language'],
  alias: 'store.MyLocale',
  data: [
    { name: 'Bahasa', value: 'MS' },
    { name: 'English', value: 'EN' },
    // { name: 'Chinese', value: 'ZH' },
  ]
});
