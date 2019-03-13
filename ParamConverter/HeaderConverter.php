<?php
/**
 * Copyright (c) 2019 Ekosport <contact@groupefrasteya.com>.
 *
 * This file is part of Ekosport website.
 *
 * Ekosport website can not be copied and/or distributed without the express permission of the CIO.
 */

namespace FLE\ScalarConverterBundle\ParamConverter;

use ReflectionException;
use ReflectionMethod;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use function preg_replace;
use function strtolower;

class HeaderConverter implements ParamConverterInterface
{
    /**
     * Checks if the object is supported.
     *
     * @param ParamConverter $configuration
     *
     * @return bool True if the object is supported, else false
     */
    public function supports(ParamConverter $configuration)
    {
        return $configuration->getClass() === null;
    }

    /**
     * Stores the object in the request.
     *
     * @param Request        $request
     * @param ParamConverter $configuration Contains the name, class and options of the object
     *
     * @return bool True if the object has been successfully set, else false
     *
     * @throws ReflectionException
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $nameToLower = strtolower(preg_replace('/[A-Z]+/', '-\\0', $configuration->getName()));

        if ($request->headers->has($nameToLower)) {
            $value = $request->headers->get($nameToLower);

            if ($value === '') {
                $value = null;
            }
            if ($value !== null) {
                $controller = $request->attributes->get('_controller');
                $rc         = new ReflectionMethod($controller);
                $parameters = $rc->getParameters();
                foreach ($parameters as $parameter) {
                    if ($parameter->getName() === $configuration->getName()) {
                        if ($parameter->getType()->getName() === 'int') {
                            $value = (int) $value;
                        }
                        if ($parameter->getType()->getName() === 'bool') {
                            $value = (bool) $value;
                        }
                        if ($parameter->getType()->getName() === 'float') {
                            $value = (float) $value;
                        }
                    }
                }
            }

            $request->attributes->set($configuration->getName(), $value);

            return true;
        }

        return false;
    }
}
