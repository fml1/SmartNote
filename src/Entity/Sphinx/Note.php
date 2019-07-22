<?php

namespace App\Entity\Sphinx;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\TextType;

/**
 * @ORM\Entity(repositoryClass="App\Repository\NoteRepository")
 * @ORM\Table(name="note_1")
 */
class Note
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * Заголовок
     * @var string|null
     * @ORM\Column(name="title", type="string", nullable=true)
     */
    private $title;
    /**
     * Исходник c HTML (атрибут)
     * @ORM\Column(name="attr_content", type="text", nullable=true)
     */
    private $attr_content;
    /**
     * Исходник (поле для индексации)
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;
    /**
     * @ORM\Column(name="tag", type="string", nullable=true)
     */
    private $tag;
    /**
     * Родительский элемент
     * @var integer|null
     * @ORM\Column(name="parent_id", type="integer", nullable=true)
     */
    private $attr_parent_id;
    /**
     * Порядоковый номер на уровне
     * @var integer|null
     * @ORM\Column(name="attr_order_id", type="integer", nullable=true)
     */
    private $attr_order_id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param string|null $content
     */
    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    /**
     * @param string|null $content
     */
    public function setContentAsString(?string $content): void
    {
        $this->content = $content;
    }

    /**
     * @return int|null
     */
    public function getAttrParentId(): ?int
    {
        return $this->attr_parent_id;
    }

    /**
     * @param int|null $attr_parent_id
     */
    public function setAttrParentId(?int $attr_parent_id): void
    {
        $this->attr_parent_id = $attr_parent_id;
    }

    /**
     * @return string|null
     */
    public function getTag() :?string
    {
        return $this->tag;
    }

    /**
     * @param string|null $tag
     */
    public function setTag(?string $tag): void
    {
        $this->tag = $tag;
    }

    /**
     * @return string|null
     */
    public function getAttrContent() :?string
    {
        return $this->attr_content;
    }

    /**
     * @param string|null $attr_content
     */
    public function setAttrContent(?string $attr_content): void
    {
        $this->attr_content = $attr_content;
    }

    /**
     * @return int|null
     */
    public function getAttrOrderId(): ?int
    {
        return $this->attr_order_id;
    }

    /**
     * @param int|null $attr_order_id
     */
    public function setAttrOrderId(?int $attr_order_id): void
    {
        $this->attr_order_id = $attr_order_id;
    }

}