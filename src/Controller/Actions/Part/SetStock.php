<?php

namespace Limas\Controller\Actions\Part;

use Doctrine\ORM\EntityManagerInterface;
use Limas\Controller\Actions\ActionUtilTrait;
use Limas\Entity\Part;
use Limas\Entity\StockEntry;
use Limas\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;


#[AsController]
class SetStock
	extends AbstractController
{
	use ActionUtilTrait;


	public function __construct(
		private readonly EntityManagerInterface $entityManager,
		private readonly UserService            $userService
	)
	{
	}

	public function __invoke(Request $request, int $id): Part
	{
		$part = $this->entityManager->find(Part::class, $id);
		if (0 !== ($correctionQuantity = $request->request->getInt('quantity') - $part->getStockLevel())) {
			$stock = (new StockEntry)
				->setUser($this->userService->getCurrentUser())
				->setStockLevel($correctionQuantity);
			if ($request->request->get('comment') !== null) {
				$stock->setComment($request->request->get('comment'));
			}
			$part->addStockLevel($stock);
			$this->entityManager->persist($stock);
			$this->entityManager->flush();
		}
		return $part;
	}
}