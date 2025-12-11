<?php

namespace App\Helpers;

class NumberToWords
{
    /**
     * Convert number to words (Kenyan Shillings format).
     */
    public static function convert(float $number, string $currency = 'KES'): string
    {
        $whole = (int) floor($number);
        $fraction = (int) round(($number - $whole) * 100);

        $words = self::numberToWords($whole);

        $result = ucfirst($words).' '.self::getCurrencyName($currency);

        if ($fraction > 0) {
            $cents = self::numberToWords($fraction);
            $result .= ' and '.ucfirst($cents).' Cents';
        }

        $result .= ' Only';

        return $result;
    }

    /**
     * Convert number to words.
     */
    protected static function numberToWords(int $number): string
    {
        if ($number == 0) {
            return 'zero';
        }

        $ones = [
            0 => '', 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four',
            5 => 'five', 6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine',
            10 => 'ten', 11 => 'eleven', 12 => 'twelve', 13 => 'thirteen',
            14 => 'fourteen', 15 => 'fifteen', 16 => 'sixteen', 17 => 'seventeen',
            18 => 'eighteen', 19 => 'nineteen',
        ];

        $tens = [
            2 => 'twenty', 3 => 'thirty', 4 => 'forty', 5 => 'fifty',
            6 => 'sixty', 7 => 'seventy', 8 => 'eighty', 9 => 'ninety',
        ];

        $hundreds = [
            1 => 'one hundred', 2 => 'two hundred', 3 => 'three hundred',
            4 => 'four hundred', 5 => 'five hundred', 6 => 'six hundred',
            7 => 'seven hundred', 8 => 'eight hundred', 9 => 'nine hundred',
        ];

        if ($number < 20) {
            return $ones[$number];
        }

        if ($number < 100) {
            $tensDigit = (int) floor($number / 10);
            $onesDigit = $number % 10;

            return $tens[$tensDigit].($onesDigit > 0 ? '-'.$ones[$onesDigit] : '');
        }

        if ($number < 1000) {
            $hundredsDigit = (int) floor($number / 100);
            $remainder = $number % 100;

            return $hundreds[$hundredsDigit].($remainder > 0 ? ' '.self::numberToWords($remainder) : '');
        }

        if ($number < 1000000) {
            $thousands = (int) floor($number / 1000);
            $remainder = $number % 1000;

            $thousandsWords = self::numberToWords($thousands).' thousand';
            $remainderWords = $remainder > 0 ? ' '.self::numberToWords($remainder) : '';

            return $thousandsWords.$remainderWords;
        }

        if ($number < 1000000000) {
            $millions = (int) floor($number / 1000000);
            $remainder = $number % 1000000;

            $millionsWords = self::numberToWords($millions).' million';
            $remainderWords = $remainder > 0 ? ' '.self::numberToWords($remainder) : '';

            return $millionsWords.$remainderWords;
        }

        // For numbers >= 1 billion
        $billions = (int) floor($number / 1000000000);
        $remainder = $number % 1000000000;

        $billionsWords = self::numberToWords($billions).' billion';
        $remainderWords = $remainder > 0 ? ' '.self::numberToWords($remainder) : '';

        return $billionsWords.$remainderWords;
    }

    /**
     * Get currency name in plural form.
     */
    protected static function getCurrencyName(string $currency): string
    {
        return match (strtoupper($currency)) {
            'KES' => 'Kenyan Shillings',
            'USD' => 'US Dollars',
            'EUR' => 'Euros',
            'GBP' => 'British Pounds',
            default => 'Shillings',
        };
    }
}
