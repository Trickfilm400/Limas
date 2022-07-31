<?php

namespace Limas\Tests;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Limas\Tests\DataFixtures\UserDataLoader;


abstract class AbstractMoveCategoryTest
	extends WebTestCase
{
	protected ReferenceRepository $fixtures;


	protected function setUp(): void
	{
		parent::setUp();
		$this->fixtures = $this->getContainer()->get(DatabaseToolCollection::class)->get()->loadFixtures([
			UserDataLoader::class,
			$this->getFixtureLoaderClass()
		])->getReferenceRepository();
	}

	public function testMoveCategory(): void
	{
		$client = static::makeAuthenticatedClient();

		$secondCategory = $this->fixtures->getReference($this->getReferencePrefix() . '.second');
		$rootCategory = $this->fixtures->getReference($this->getReferencePrefix() . '.root');

		$iriConverter = $this->getContainer()->get('api_platform.iri_converter');
		$iri = $iriConverter->getIriFromItem($secondCategory) . '/move';
		$targetIri = $iriConverter->getIriFromItem($rootCategory);

		$client->request(
			'PUT',
			$iri,
			['parent' => $targetIri],
			[],
			['CONTENT_TYPE' => 'application/x-www-form-urlencoded']
		);

		$this->assertEquals($rootCategory->getId(), $secondCategory->getParent()->getId());
		$this->assertEquals('Root Node ➤ Second Category', $secondCategory->getCategoryPath());
	}

	abstract public function getFixtureLoaderClass();

	abstract public function getReferencePrefix();
}
