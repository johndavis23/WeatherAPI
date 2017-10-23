<?php

namespace App\Util;
/**
 * Description of Util
 *
 * @author jdavis
 */
include_once("Config/config.php");

/*static*/ class Util 
{
    public static function error_log($message )
    {
        $first = true;
        $bts =  debug_backtrace();
        $stack_trace_string = "Stack Trace: \n";
        foreach($bts as $bt)
        {
            if(!$first){$first = false;}else{
                $stack_trace_string.="From: ". @$bt['file'] . " on Line:  (". @$bt['line'].")\n";
            }
        }
        error_log("\n".$message."\n".$stack_trace_string);
    }
	
    public static function error_log_stacktrace()
    {
        $first = true;
        $bts =  debug_backtrace();
        foreach($bts as $bt)
        {
            if(!$first){$first = false;}else{
                error_log("Called from: ". $bt['file'] . ' line  '. $bt['line']);
            }
        }
                 
    }
}
