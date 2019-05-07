<?php declare(strict_types=1);

namespace kornrunner\Serializer;

use InvalidArgumentException;
use Mdanter\Ecc\Crypto\Signature\Signature;
use Mdanter\Ecc\Crypto\Signature\SignatureInterface;

class HexSignatureSerializer
{
    public function serialize(SignatureInterface $signature): string {

        // echo "\r\n\r\nhexSerialize running with \r\n";
        // print_r($signature);
        // echo "\r\n";

        $r = $signature->getR();
        $s = $signature->getS();
        $result = str_pad(gmp_strval($r, 16), 64, '0', STR_PAD_LEFT) . str_pad(gmp_strval($s, 16), 64, '0', STR_PAD_LEFT);

        // echo "\r\n\r\nhexSerializeRan with \r\nr:" . $r . " \r\n\r\ns:" . $s . "\r\n\r\nhex: " . $result . "\r\n";

        return $result;
    }

    public function parse(string $binary): SignatureInterface {

        if ( mb_strlen($binary) === 50 ) {
            echo "\r\nmb_strlen(binary) is 50:" . mb_strlen($binary) . "\r\n";
        } else {
            echo "\r\nmb_strlen(binary) is not 50:" . mb_strlen($binary) . "\r\n";
        }

        $binary_lower = mb_strtolower($binary);

        if (strpos($binary_lower, '0x') >= 0) {
            $count = 1;
            $binary_lower = str_replace('0x', '', $binary_lower, $count);
        }


        // if (mb_strlen($binary_lower) !== 128) {
        //     throw new InvalidArgumentException('Binary string was not correct.');
        // }
        echo "\r\nprocessing signature\r\n" . $binary . "\r\nwith length: " . mb_strlen($binary_lower) . "\r\n";

        if (mb_strlen($binary_lower) === 128) {

            $r = mb_substr($binary_lower, 0, 64);
            $s = mb_substr($binary_lower, 64, 64);

        } else if (mb_strlen($binary_lower) === 60) {

            $r = mb_substr($binary_lower, 0, 32);
            $s = mb_substr($binary_lower, 32, mb_strlen($binary_lower));

        } else {

            throw new InvalidArgumentException('Binary string was not correct.');
        
        }


        return new Signature(
            gmp_init($r, 16),
            gmp_init($s, 16)
        );
    }
}
