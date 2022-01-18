<?php

namespace LegacyFighter\Cabs\Service\Symfony;

use LegacyFighter\Cabs\DTO\CarTypeDTO;
use LegacyFighter\Cabs\DTO\ClientDTO;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class DTOArgumentResolver implements ArgumentValueResolverInterface
{
    private SerializerInterface $serializer;

    public function __construct()
    {
        $this->serializer = new Serializer(
            [new PropertyNormalizer(null, null, new ReflectionExtractor())],
            [new JsonEncoder()]
        );
    }


    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return str_starts_with($argument->getType(), 'LegacyFighter\Cabs\DTO');
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        try {
            yield $this->serializer->deserialize($request->getContent(), $argument->getType(), 'json');
        } catch (ExceptionInterface $exception) {
            throw new BadRequestHttpException($exception->getMessage(), $exception);
        }
    }
}
