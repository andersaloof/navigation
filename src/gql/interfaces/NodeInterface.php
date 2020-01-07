<?php
namespace verbb\navigation\gql\interfaces;

use verbb\navigation\elements\Node;
use verbb\navigation\gql\types\generators\NodeGenerator;
use verbb\navigation\gql\arguments\NodeArguments;
use verbb\navigation\gql\interfaces\NodeInterface as NodeInterfaceLocal;

use craft\gql\base\InterfaceType as BaseInterfaceType;
use craft\gql\interfaces\Structure;
use craft\gql\types\DateTime;
use craft\gql\TypeLoader;
use craft\gql\GqlEntityRegistry;
use craft\helpers\Gql;

use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\Type;

class NodeInterface extends Structure
{
    // Public Methods
    // =========================================================================

    public static function getTypeGenerator(): string
    {
        return NodeGenerator::class;
    }

    public static function getType($fields = null): Type
    {
        if ($type = GqlEntityRegistry::getEntity(self::getName())) {
            return $type;
        }

        $type = GqlEntityRegistry::createEntity(self::getName(), new InterfaceType([
            'name' => static::getName(),
            'fields' => self::class . '::getFieldDefinitions',
            'description' => 'This is the interface implemented by all nodes.',
            'resolveType' => function(Node $value) {
                return $value->getGqlTypeName();
            },
        ]));

        NodeGenerator::generateTypes();

        return $type;
    }

    public static function getName(): string
    {
        return 'NodeInterface';
    }

    public static function getFieldDefinitions(): array
    {
        return array_merge(parent::getFieldDefinitions(), self::getConditionalFields(), [
            'elementId' => [
                'name' => 'elementId',
                'type' => Type::int(),
                'description' => 'The ID of the element this node is linked to.'
            ],
            'navId' => [
                'name' => 'navId',
                'type' => Type::int(),
                'description' => 'The ID of the navigation this node belongs to.'
            ],
            'navHandle' => [
                'name' => 'navHandle',
                'type' => Type::string(),
                'description' => 'The handle of the navigation this node belongs to.'
            ],
            'navName' => [
                'name' => 'navName',
                'type' => Type::string(),
                'description' => 'The name of the navigation this node belongs to.'
            ],
            'type' => [
                'name' => 'type',
                'type' => Type::string(),
                'description' => 'The type of node this is.'
            ],
            'classes' => [
                'name' => 'classes',
                'type' => Type::string(),
                'description' => 'Any additional classes for the node.'
            ],
            'newWindow' => [
                'name' => 'newWindow',
                'type' => Type::string(),
                'description' => 'Whether this node should open in a new window.'
            ],
            'url' => [
                'name' => 'url',
                'type' => Type::string(),
                'description' => 'The node’s full URL',
            ],
            'children' => [
                'name' => 'children',
                'args' => NodeArguments::getArguments(),
                'type' => Type::listOf(NodeInterfaceLocal::getType()),
                'description' => 'The node’s children. Accepts the same arguments as the `nodes` query.'
            ],
            'parent' => [
                'name' => 'parent',
                'type' => NodeInterfaceLocal::getType(),
                'description' => 'The node’s parent.'
            ],
        ]);
    }

    protected static function getConditionalFields(): array
    {
        if (Gql::canQueryUsers()) {
            return [
                'userId' => [
                    'name' => 'userId',
                    'type' => Type::int(),
                    'description' => 'The ID of the author of this node.'
                ],
            ];
        }

        return [];
    }
}