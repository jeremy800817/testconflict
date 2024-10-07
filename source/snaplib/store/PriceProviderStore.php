<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////

Namespace Snap\store;

Use Snap\object\vendor;
Use PDO, PDOException, PDORow;

/**
* This class implements a basic data storage service that will persist the data into database.  It supports
* operations that deals with single table and is associated directly with an IEntity item interface.  This data
* store will also support views etc as in the old interface in mxObject::getAll()
*
* @author  Megat <zulkhairi@silverstream.my>
* @version 1.0
* @package common
*/

class PriceProviderStore extends redisarraydbdatastore {

	/**
	 * This function returns the price levels available
	 * 
	 * @return Array of price levels
	 */
	public function getForPartnerByProduct(\Snap\object\Partner $partner, \Snap\object\Product $product)
    {
        $provider = $this->searchTable()
                    ->Select()
                    ->where('pricesourceid', $partner->pricesourceid)
                    ->andWhere('productcategoryid', $product->categoryid)
                    ->andWhere('status', 1)
                    ->one();
        if(! $provider) {
            throw \Snap\api\exception\PriceProviderNotFound::fromTransaction($partner, ['pricesourceid' => $partner->pricesourceid, 'categoryid' => $product->categoryid]);
        }
        return $provider;
    }
}
?>