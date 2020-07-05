<?php

declare(strict_types = 1);

namespace App\Controller\Concerns;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\XmlFileLoader;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

trait UsesXmlMapping
{
	/**
	 * @var SerializerInterface
	 */
	private SerializerInterface $serializer;
	
	/**
	 * @param  string  $projectDir
	 */
	public function createSerializer(string $projectDir): void
	{
		$file                 = $projectDir . '/config/serializer/groups.xml';
		$classMetaDataFactory = new ClassMetadataFactory(new XmlFileLoader($file));
		$encoders             = [new JsonEncoder()];
		$normalizers          = [new DateTimeNormalizer(['datetime_format'=>'d.m.Y H:i']), new ObjectNormalizer($classMetaDataFactory)];
		
		$this->serializer = new Serializer($normalizers, $encoders);
	}
	
	/**
	 * @return SerializerInterface
	 */
	public function getSerializer(): SerializerInterface
	{
		return $this->serializer;
	}
}