<?php
namespace AGTI\Cliente\Infrastructure\Serializer;

use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer as SerializerSerializer;

class Serializer
{
    public static function buildSerializer()
    {
        $encoders = [new JsonEncoder(), new XmlEncoder()];

        $extractor = new PropertyInfoExtractor([], [new ReflectionExtractor()]);

        $normalizer = new ObjectNormalizer(null, null, null, $extractor);
        $normalizer->setCircularReferenceHandler(function(){
            return -1;
        });

        $normalizers = [$normalizer];
        $serializer = new SerializerSerializer($normalizers, $encoders);

        return $serializer;
    }
}