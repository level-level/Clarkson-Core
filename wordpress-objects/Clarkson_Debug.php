<?php

class Clarkson_Debug {
    /**
     * Print the call stack from any function by calling callStack();
     * Usage: Clarkson_Debug::callStack();
     * @print prints the function callstack.
     */
    public static function callStack() {
        $stacktrace = debug_backtrace();

        print "<b>Call Stack:</b><br>";
        print str_repeat("=", 50) ."\n";
        $i = 1;
        foreach($stacktrace as $node) {
            print "<br>". "<b>$i.</b> ".basename($node['file']) .":" .$node['function'] ."(" .$node['line'].")\n";
            $i++;
        }
        print "<br>" . str_repeat("=", 50) ."\n";
    }
}