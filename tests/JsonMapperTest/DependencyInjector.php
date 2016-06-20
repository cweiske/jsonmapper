<?php
class JsonMapperTest_DependencyInjector extends \apimatic\jsonmapper\JsonMapper
{
    /**
     * Create a new object of the given type.
     *
     * This method exists to be overwritten in child classes,
     * so you can do dependency injection or so.
     *
     * @param string  $class        Class name to instantiate
     * @param boolean $useParameter Pass $parameter to the constructor or not
     * @param mixed   $parameter    Constructor parameter
     *
     * @return object Freshly created object
     */
    public function createInstance(
        $class, $useParameter = false, $parameter = null
    ) {
        $object = parent::createInstance($class, $useParameter, $parameter);

        //dummy dependency injection
        $object->db = 'database';

        return $object;
    }
}
?>