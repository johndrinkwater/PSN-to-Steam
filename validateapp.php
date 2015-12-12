<?php

// We want to take the supplied argument, parse our file, validate (phpunit? or otherwise),
// do additional checks that are not data-conformity issues, and print a listing for that
// app (along with mappings, duplicates or otherwise, holes, DLC, etc)

if ( $argc > 1 ) {
  if ( is_integer( $argv[1] ) ) {
    // do it here
  } else {
    echo "That is not a valid appid (Needs to be numeric).\n";
  }
} else {
  echo "You need to supply an appid.\n";
}
