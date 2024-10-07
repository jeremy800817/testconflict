Ext.define('snap.model.PartnerServices', {
    extend: 'snap.model.Base',
    fields: [
        	{type: 'int', name: 'id'},
			{type: 'int', name: 'partnerid'},
			{type: 'int', name: 'partnersapgroup'},
			{type: 'int', name: 'productid'},
            {type: 'int', name: 'pricesourcetypeid'},
			{type: 'string', name: 'refineryfee'},
			{type: 'string', name: 'premiumfee'},
			{type: 'string', name: 'redemptionpremiumfee'},
			{type: 'string', name: 'redemptioncommission'},
			{type: 'string', name: 'redemptioninsurancefee'},
			{type: 'string', name: 'redemptionhandlingfee'},
			{type: 'int', name: 'includefeeinprice'},
			{type: 'int', name: 'canbuy'},
			{type: 'int', name: 'cansell'},
			{type: 'int', name: 'canqueue'},
			{type: 'int', name: 'canredeem'},
			{type: 'string', name: 'buyclickminxau'},
			{type: 'string', name: 'buyclickmaxxau'},
			{type: 'string', name: 'sellclickminxau'},
			{type: 'string', name: 'sellclickmaxxau'},
			{type: 'string', name: 'dailybuylimitxau'},
			{type: 'string', name: 'dailyselllimitxau'},
			{type: 'int', name: 'status'}		

    ]
});
