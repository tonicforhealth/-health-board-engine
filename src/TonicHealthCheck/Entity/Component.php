<?php

namespace TonicHealthCheck\Entity;

use Doctrine\ORM\Mapping\Entity;

/**
 * TonicHealthCheck\Entity\Component;.
 *
 * @Entity(repositoryClass="ComponentRepository")
 * @Table(name="component")
 */
class Component
{
    /**
     * @Id
     * @Column(type="integer")
     */
    private $id;

    /**
     * @Column(type="string", length=128)
     */
    private $name;

    /**
     * @Column(type="string", nullable=true, length=256)
     */
    private $description;

    /**
     * @Column(type="string", nullable=true, length=128)
     */
    private $link;

    /**
     * @Column(type="integer", options={"unsigned":true, "default":0})
     */
    private $status;

    /**
     * @Column(type="integer", nullable=true, name="`order`", options={"unsigned":true, "default":0})
     */
    private $order;

    /**
     * @Column(type="integer", nullable=true)
     */
    private $group_id;

    /**
     * @Column(type="boolean", nullable=true)
     */
    private $enabled;

    /**
     * Set id.
     *
     * @param int $id
     *
     * @return Component
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Component
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return Component
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set link.
     *
     * @param string $link
     *
     * @return Component
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link.
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set status.
     *
     * @param int $status
     *
     * @return Component
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set order.
     *
     * @param int $order
     *
     * @return Component
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order.
     *
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set groupId.
     *
     * @param int $groupId
     *
     * @return Component
     */
    public function setGroupId($groupId)
    {
        $this->group_id = $groupId;

        return $this;
    }

    /**
     * Get groupId.
     *
     * @return int
     */
    public function getGroupId()
    {
        return $this->group_id;
    }

    /**
     * Set enabled.
     *
     * @param bool $enabled
     *
     * @return Component
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled.
     *
     * @return bool
     */
    public function getEnabled()
    {
        return $this->enabled;
    }
}
