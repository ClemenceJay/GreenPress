<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'commande')]
    private ?User $User = null;

    #[ORM\Column]
    private ?\DateTime $date = null;

    #[ORM\ManyToOne(inversedBy: 'commande')]
    private ?Status $status = null;

    /**
     * @var Collection<int, ProductCommande>
     */
    #[ORM\OneToMany(targetEntity: ProductCommande::class, mappedBy: 'commande')]
    private Collection $productCommande;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $stripeSessionId = null;

    public function __construct()
    {
        $this->ProductCommande = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): static
    {
        $this->User = $User;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): static
    {
        
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, ProductCommande>
     */
    public function getProductCommande(): Collection
    {
        return $this->ProductCommande;
    }

    public function addProductCommande(ProductCommande $productCommande): static
    {
        if (!$this->ProductCommande->contains($productCommande)) {
            $this->ProductCommande->add($productCommande);
            $productCommande->setCommande($this);
        }

        return $this;
    }

    public function removeProductCommande(ProductCommande $productCommande): static
    {
        if ($this->ProductCommande->removeElement($productCommande)) {
            // set the owning side to null (unless already changed)
            if ($productCommande->getProductCommande() === $this) {
                $productCommande->setProductCommande(null);
            }
        }

        return $this;
    }

    public function getStripeSessionId(): ?string
    {
        return $this->stripeSessionId;
    }

    public function setStripeSessionId(?string $stripeSessionId): static
    {
        $this->stripeSessionId = $stripeSessionId;

        return $this;
    }
}
