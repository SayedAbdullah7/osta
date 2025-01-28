<?php

namespace App\Helpers;


class Column
{
    public string $data;
    public ?string $name;
    public ?string $title;
    public bool $searchable;
    public bool $orderable;

    public function __construct(
        string $data,
        ?string $name = null,
        ?string $title = null,
        bool $searchable = true,
        bool $orderable = true
    ) {
        $this->data = $data;
        $this->name = $name ?? $data;
        $this->title = $title ?? str_replace('_', ' ', ucfirst(preg_replace('/_id$/', '', $data)));
        $this->searchable = $searchable;
        $this->orderable = $orderable;
    }

    /**
     * Static factory method to create a Column instance.
     *
     * @param string $data
     * @return self
     */
    public static function create(string $data): self
    {
        return new self($data);
    }

    /**
     * Fluent method to set the name.
     *
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Fluent method to set the title.
     *
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Fluent method to set searchable.
     *
     * @param bool $searchable
     * @return $this
     */
    public function setSearchable(bool $searchable): self
    {
        $this->searchable = $searchable;
        return $this;
    }

    /**
     * Fluent method to set orderable.
     *
     * @param bool $orderable
     * @return $this
     */
    public function setOrderable(bool $orderable): self
    {
        $this->orderable = $orderable;
        return $this;
    }

    /**
     * Convert column to array format.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'data' => $this->data,
            'name' => $this->name,
            'title' => $this->title,
            'searchable' => $this->searchable,
            'orderable' => $this->orderable,
        ];
    }

    /**
     * Implement JsonSerializable to automatically convert to array.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'data' => $this->data,
            'name' => $this->name,
            'title' => $this->title,
            'searchable' => $this->searchable,
            'orderable' => $this->orderable,
        ];
    }

}
