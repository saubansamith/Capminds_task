<?php
function validateVital($vitalData, $ruleFunction){
    return $ruleFunction($vitalData);
}
?>