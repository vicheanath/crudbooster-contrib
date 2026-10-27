<?php 
namespace crocodicstudio\crudbooster\helpers;

/**
 * @deprecated 7.2 Alias of CRUDBooster; see the replacement on each CRUDBooster method. See
 *     docs/en/deprecations.md.
 */
class CB extends CRUDBooster  {
	//This CB class is for alias of CRUDBooster class
	
	
    //alias of echoSelect2Mult
    public function ES2M($values, $table, $id, $name) {
        return CRUDBooster::echoSelect2Mult($values, $table, $id, $name);
    }
}
