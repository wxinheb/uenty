<?php

namespace Faker\Provider\ar_JO;

class Company extends \Faker\Provider\Company
{
    protected static $formats = array(
        '{{lastName}} {{companySuffix}}',
        '{{companyPrefix}} {{lastName}} {{companySuffix}}',
        '{{companyPrefix}} {{lastName}}',
    );

    protected static $bsWords = array(
        array()
    );

    protected static $catchPhraseWords = array(
        array('الخدمات','الحلول','الانظمة'),
        array(
            'الذهبية','الذكية','المتطورة','المتقدمة', 'الدولية', 'المتخصصه', 'السريعة',
            'المثلى', 'الابداعية', 'المتكاملة', 'المتغيرة', 'المثالية'
            ),
    );

    protected static $companyPrefix = array('شركة','مؤسسة','مجموعة','مكتب','أكاديمية','معرض');

    protected static $companySuffix = array('وأولاده', 'للمساهمة المحدودة', ' ذ.م.م', 'مساهمة عامة', 'وشركائه');

    
    public function companyPrefix()
    {
        return static::randomElement(self::$companyPrefix);
    }

    
    public function catchPhrase()
    {
        $result = array();
        foreach (static::$catchPhraseWords as &$word) {
            $result[] = static::randomElement($word);
        }

        return join($result, ' ');
    }

    
    public function bs()
    {
        $result = array();
        foreach (static::$bsWords as &$word) {
            $result[] = static::randomElement($word);
        }

        return join($result, ' ');
    }
}
