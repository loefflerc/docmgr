<?php

/*
    Originally
    By: Jon S. Stevens jon@clearink.com
    Copyright 1998 Jon S. Stevens, Clear Ink
    This code has all the normal disclaimers.
    It is free for any use, just keep the credits intact.

Enacements and modifications:

           By:  Shane Y. Gibson  shane@tuna.org
Organization:  The Unix Network Archives (http://www.tuna.org./)
         Date:  November 16th, 1998
      Changes:  Added **all** comments, as original code lacked them.
                Added some return codes to include a bit more description
                for useability.
           By:    Frank Vogel vogel@simec.com
Organization:  Simec Corp. (http://www.simec.com)
     Date:  June 13th, 2000
      Changes:  Check for MX records for each qualification step of  the domain name
                Use nobdy@$SERVER_NAME as MAIL FROM: argument

I disclaim nothing...nor do I claim anything...but
it would be nice if you included this disclaimer...

*/

/*  This function takes in an email address (say 'shane@tuna.org'
*  and tests to see if it's a valid email address.
*
*  An array with the results is passed back to the caller.
*
*  Possible result codes for the array items are:
*
*  Item 0:  [true|false]        true for valid email address
*                    false for NON-valid email address
*
*  Item 1:  [SMTP Code]        if a valid MX mail server found, then
*                    fill this array in with failed SMTP
*                    reply codes
*
*  Item 2:  [true|false]        true for valid mail server found for
*                    host/domain
*                    false if no valid mail server found
*
*  Item 3:  [MX server]        if a valid MX host was found and
*                    connected to then fill in this item
*                    with the MX server hostname
*
*  EXAMPLE code for use is at the very end of this function.
*/


function validateEmail ( $email )
{    
    // used for SMTP HELO argument
    global $SERVER_NAME;
            
    // initialize the return values as if no MX record appears for the specified domain
    $return[0] = false;
    $return[1] = "Invalid email address (bad domain name)";
    $return[2] = false;
    $return[3] = "";
    

    // assign our user part and domain parts respectively to seperate             
    // variables
    list ( $user, $domain )  = split ( "@", $email, 2 );
    
    // split up the domain name into sub-parts
    $arr = explode ( ".", $domain );

    // figure out how many parts to the host/domain name portion there are
    $count = count ( $arr );
                
    // flag to indicate success
    $bSuccess = false;
        
    // we try this for each qualification step of domain name
    // (from full qualified to TopLevel)
    for ( $i = 0; $i < $count - 1 && !$bSuccess; $i = $i +    1 )
    {
        // create the domain name
        $domain = "";    
        for ( $j = $i; $j < $count; $j = $j + 1 )
        {            
            $domain = $domain . $arr[$j];
            if ( $j < $count - 1 )
                $domain = $domain . ".";
        }        
        
        // check that an MX record exists for Top-Level Domain, and if so
        // start our email address checking
        if ( checkdnsrr ( $domain, "MX" ) )     
        {
            // Okay...valid dns reverse record; test that MX record for
            // host exists, and then fill the 'mxhosts' and 'weight'
            // arrays with the correct information

            if ( getmxrr ( $domain, $mxhosts, $weight ) )
            {
                // sift through the 'mxhosts' connecting to each host
                for ( $i = 0; $i < count ( $mxhosts ); $i++ )
                {
                    // open socket on port 25 to mxhosts, setting                         
                    // returned file pointer to the variable 'fp'
                    $fp = fsockopen ( $mxhosts[$i], 25 );

                    // if the 'fp' was set, then goto work
                    if ( $fp )
                    {
                        // work variables
                        $s = 0;
                        $c = 0;
                        $out = "";
            
                        // set our created socket for 'fp' to     
                        // non-blocking mode
                        // so our fgets() calls will return
                        // right away
                        set_socket_blocking ( $fp, false );

                        // as long as our 'out' variable has a
                        // null value ("")
                        // keep looping (do) until we get
                        // something
                        //
                        do
                        {
                            // output of the stream assigned
                            // to 'out' variable
                            $out = fgets ( $fp, 2500 );

                            // if we get an "220" code (service ready code (i.e greeting))
                            // increment our work (code (c)) variable, and null
                            // out our output variable for a later loop test
                            //
                            if ( ereg ( "^220", $out ) )
                            {
                                $s = 0;
                                $out = "";
                                $c++;
                                $return[2] = true;
                                $return[3] = $mxhosts[$i];
                            }
                            // elseif c is greater than 0
                            // and 'out' is null (""),
                            // we got a code back from some
                            // server, and we've passed
                            // through this loop at least
                            // once
                            else if ( ( $c > 0 ) && ( $out == "" ) )
                            { 
                                $return[2] = true;
                                break; 
                            }

                            // else increment our 's'
                            // counter
                            else
                            { $s++;    }
                        
                            // and if 's' is 9999, break, to
                            // keep from looping
                            // infinetly
                            if ( $s == 9999 ) { break; }
                        
                        } while ( $out == "" );

                        // reset our file pointer to blocking
                        // mode, so we wait
                        // for communication to finish before
                        // moving on...
                        set_socket_blocking ( $fp, true );

                        // talk to the MX mail server,
                        // validating ourself (HELO)
                        fputs ( $fp, "HELO $SERVER_NAME\n" );

                        // get the mail servers reply, assign to
                        // 'output' (ignored)
                        $output = fgets ( $fp, 2000 );

                        // give a bogus "MAIL FROM:" header to
                        // the server
                        fputs ( $fp, "MAIL FROM: <nobody@" . $SERVER_NAME . ">\n" );
                        // get output again (ignored)
                        $output = fgets ( $fp, 2000 );

                        // give RCPT TO: header for the email
                        // address we are testing
                        fputs ( $fp, "RCPT TO: <$email>\n" );                

                        // get final output for validity testing
                        // (used)
                        $output = fgets ( $fp, 2000 );

                        // test the reply code from the mail
                        // server for the 250 (okay) code
                        if ( ereg ( "^250", $output ) )
                        {
                            // set our true/false(ness)
                            // array item for testing
                            $return[0] = true;
                        }
                        else
                        {
                            // otherwise, bogus address,
                            // fillin the 2nd array item
                            // with the mail servers reply
                            // code for user to test if they
                            // want
                            $return[0] = false;
                            $return[1] = $output;
                        }
                    
                        // tell the mail server we are done
                        // talking to it
                        fputs ( $fp, "QUIT\n" );

                        // close the file pointer
                        fclose( $fp );

                        // if we got a good value break,
                        // otherwise, we'll keep
                        // trying MX records until we get a good
                        // value, or we
                        // exhaust our possible MX servers
                        if ( $return[0] == true )
                        {
                            $bSuccess = true;
                            break;
                        }
                    }
                }
            }
        }
    } 
        
    // return the array for the user to test against
    return $return;
}

?>