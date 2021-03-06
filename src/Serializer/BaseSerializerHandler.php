<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\Serializer;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\VisitorInterface;
use Sonata\CoreBundle\Model\ManagerInterface;

/**
 * @author Sylvain Deloux <sylvain.deloux@ekino.com>
 */
abstract class BaseSerializerHandler implements SerializerHandlerInterface
{
    /**
     * @var ManagerInterface
     */
    protected $manager;

    /**
     * @var string[]
     */
    protected static $formats;

    /**
     * @param ManagerInterface $manager
     */
    public function __construct(ManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param string[] $formats
     */
    final public static function setFormats(array $formats)
    {
        static::$formats = $formats;
    }

    /**
     * @param string $format
     */
    final public static function addFormat($format)
    {
        static::$formats[] = $format;
    }

    public static function getSubscribingMethods()
    {
        // NEXT_MAJOR : remove this block
        if (null === static::$formats) {
            static::$formats = ['json', 'xml', 'yml'];
            @trigger_error(
                '$formats has been set to default array("json", "xml", "yml"). Setting $formats to a
                default array is deprecated since version 3.0 and will be removed in 4.0. Use SonataCoreBundle
                configuration to add default serializer formats.',
                E_USER_DEPRECATED
            );
        }

        $type = static::getType();
        $methods = [];

        foreach (static::$formats as $format) {
            $methods[] = [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => $format,
                'type' => $type,
                'method' => 'serializeObjectToId',
            ];

            $methods[] = [
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format' => $format,
                'type' => $type,
                'method' => 'deserializeObjectFromId',
            ];
        }

        return $methods;
    }

    /**
     * Serialize data object to id.
     *
     * @param object $data
     *
     * @return int|null
     */
    public function serializeObjectToId(VisitorInterface $visitor, $data, array $type, Context $context)
    {
        $className = $this->manager->getClass();

        if ($data instanceof $className) {
            return $visitor->visitInteger($data->getId(), $type, $context);
        }
    }

    /**
     * Deserialize object from its id.
     *
     * @param int $data
     *
     * @return object|null
     */
    public function deserializeObjectFromId(VisitorInterface $visitor, $data, array $type)
    {
        return $this->manager->findOneBy(['id' => $data]);
    }
}

class_exists(\Sonata\CoreBundle\Serializer\BaseSerializerHandler::class);
