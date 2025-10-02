<?php
/*
* Provee mecanismos para la transformacion a un string JSON del objeto
* sin exponer atributos sensibles los cuales nunca seran enviados
* (como contrasenas, excluyendo CI o telefonos los cuales si pueden ser necesario ser enviados).
*
* Esta implementacion facilita el transporte de datos sin requerir de DTOs.
* Los DTOs son clases muy similares a los modelos los cuales excluyen la informacion sensible segun
* sea necesario y contiene formatos transferibles por redes.
*/
abstract class BaseModel
{
    /*
    * Los atributos del objeto a ser ignorados por los metodos
    * de serializacion JSON que provee `BaseModel`.
    */
    protected array $exclude;

    public function __construct(string ...$exclude)
    {
        $this->exclude = $exclude;
    }

    /**
    * Obtiene los atributos publicos de este objeto y sus valores.
    */
    private function getPublicProperties(): array
    {
        $reflect = new ReflectionClass($this);
        $props = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);

        $data = [];
        foreach ($props as $prop) {
            $name = $prop->getName();
            $data[$name] = $this->$name;
        }

        return $data;
    }

    /**
    * Convert the object to JSON, excluding predefined fields.
    * Convierte el objeto a un string JSON, excluyendo los atributos
    * mencionados en `$exclude`.
    */
    public function toJson(): string
    {
        $data = $this->getPublicProperties();

        foreach ($this->exclude as $field)
        {
            unset($data[$field]);
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
    * Convert the object to JSON, excluding predefined fields.
    * Convierte el objeto a un string JSON, excluyendo los atributos
    * mencionados en `$exclude` + additionals provided in the `exclude` parameter.
    */
    public function toJsonIgnore(string ...$exclude): string
    {
        $data = $this->getPublicProperties();

        $allToExclude = array_merge($this->exclude, $exclude);
        foreach ($allToExclude as $field)
        {
            unset($data[$field]);
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
?>
