<?php

declare(strict_types=1);

/*
 * @license    MIT License
 */

namespace Geocoder\Provider\Sputnik\Model;

use Geocoder\Model\Address;

/**
 * @author
 */
final class SputnikAddress extends Address
{
    /**
     * @var string|null
     */
    private $precision;

    /**
     * The name of this location.
     *
     * @var string|null
     */
    private $name;

    /**
     * The kind of this location.
     *
     * @var string|null
     */
    private $kind;

    /**
     * @return string|null
     */
    public function getPrecision()
    {
        return $this->precision;
    }

    /**
     * @param string|null $precision
     *
     * @return SputnikAddress
     */
    public function withPrecision(string $precision = null): self
    {
        $new = clone $this;
        $new->precision = $precision;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     *
     * @return SputnikAddress
     */
    public function withName(string $name = null): self
    {
        $new = clone $this;
        $new->name = $name;

        return $new;
    }

    /**
     * @return string|null
     */
    public function getKind(): string
    {
        return $this->kind;
    }

    /**
     * @param string|null $kind
     *
     * @return SputnikAddress
     */
    public function withKind(string $kind = null): self
    {
        $new = clone $this;
        $new->kind = $kind;

        return $new;
    }
}
