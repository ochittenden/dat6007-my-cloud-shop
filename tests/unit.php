<?php require_once __DIR__.'/../includes/totp.php';
function assert_true(bool $test,string $message): void {if(!$test)throw new RuntimeException($message);}
$secret='GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';
assert_true(base32_decode_secret($secret)==='12345678901234567890','base32 decoding');
assert_true(strlen(totp_code($secret,59))===6,'TOTP width');
assert_true(totp_verify($secret,totp_code($secret,time())),'valid TOTP');
assert_true(!totp_verify($secret,'abcdef'),'invalid TOTP');
echo "TOTP unit tests passed.\n";
