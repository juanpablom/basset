<?php

use Mockery as m;

class BuilderTest extends PHPUnit_Framework_TestCase {
    var $buildPattern = '[collection-name]-[date:m-d-Y]-release1';

    /**
     * Tear down operations
     */
    public function tearDown()
    {
        #m::close();
    }

    /**
     * Setup operations
     */
    public function setUp()
    {
        $this->files = m::mock('Illuminate\Filesystem\Filesystem');
        $this->files->shouldReceive('exists')->once()->with('foo')->andReturn(true);
        $this->files->shouldReceive('put')->andReturn(1);

        $this->manifest = m::mock('Basset\Manifest\Manifest');
        $this->manifest->shouldReceive('make')->andReturn(new \Basset\Manifest\Entry());

        $this->directory = m::mock('Basset\Directory');
        $this->collection = m::mock('Basset\Collection');
        $this->builder = new Basset\Builder\Builder($this->files, $this->manifest, 'foo', $this->buildPattern);
    }

    /**
     *
     */
    public function testBuilderChecksForBuildPathAndMakesDirectoryIfItDoesNotExist()
    {
        $this->files->shouldReceive('exists')->once()->with('foo')->andReturn(false);
        $this->files->shouldReceive('makeDirectory')->once()->with('foo')->andReturn(true);

        $builder = new Basset\Builder\Builder($this->files, $this->manifest, 'foo', $this->buildPattern);
    }

    /**
     * @expectedException Basset\Exceptions\BuildNotRequiredException
     */
    public function testBuildingEmptyProductionCollectionThrowsBuildNotRequiredException()
    {
        $collection = m::mock('Basset\Collection');
        $collection->shouldReceive('getAssetsWithoutRaw')->once()->with('stylesheets')->andReturn(new Illuminate\Support\Collection);
        $collection->shouldReceive('getIdentifier')->once()->andReturn('foo');

        $this->manifest->shouldReceive('make')->once()->with('foo')->andReturn($entry = m::mock('Basset\Manifest\Entry'));
        $entry->shouldReceive('resetProductionFingerprint')->once()->with('stylesheets');

        $this->builder->buildAsProduction($collection, 'stylesheets');
    }

    /**
     * @expectedException Basset\Exceptions\BuildNotRequiredException
     */
    public function testBuildingExistingProductionCollectionThrowsBuildNotRequiredException()
    {
        $this->collection->shouldReceive('getAssetsWithoutRaw')->once()->with('stylesheets')->andReturn(new Illuminate\Support\Collection(array(
            $asset = m::mock('Basset\Asset')
        )));
        $asset->shouldReceive('build')->once()->andReturn('body { }');

        $this->collection->shouldReceive('getIdentifier')->once()->andReturn('foo');
        $this->collection->shouldReceive('getExtension')->once()->with('stylesheets')->andReturn('css');

        $entry = m::mock('Basset\Manifest\Entry');
        $this->manifest->shouldReceive('make')->once()->with('foo')->andReturn($entry);
        $entry->shouldReceive('getProductionFingerprint')->with('stylesheets')->andReturn($fingerprint = 'foo-'.md5('body { }').'.css');

        $this->files->shouldReceive('exists')->once()->with('foo/'.$fingerprint)->andReturn(true);

        $this->builder->buildAsProduction($this->collection, 'stylesheets');
    }

    /**
     * Test building a production collection and writes tot he file system and set a production fingerprint
     */
    public function testBuildingProductionCollectionWritesToFilesystemAndSetsProductionFingerprint()
    {
        $newCollection = new Illuminate\Support\Collection( array( $asset = m::mock('Basset\Asset') ) );
        $this->collection->shouldReceive('getAssetsWithoutRaw')->once()->with('stylesheets')->andReturn($newCollection);
        $asset->shouldReceive('build')->once()->andReturn('body { }');

        $this->collection->shouldReceive('getIdentifier')->once()->andReturn('foo');
        $this->collection->shouldReceive('getExtension')->once()->with('stylesheets')->andReturn('css');

        $fingerprint = 'foo-'.md5('body { }').'.css';

        $this->manifest->shouldReceive('make')->once()->with('foo')->andReturn($entry = m::mock('Basset\Manifest\Entry'));
        $entry->shouldReceive('getProductionFingerprint')->with('stylesheets')->andReturn(null);
        $entry->shouldReceive('setProductionFingerprint')->with('stylesheets', $fingerprint);

        $this->files->shouldReceive('put')->once()->with('foo/'.$fingerprint, 'body { }');

        $this->builder->buildAsProduction($this->collection, 'stylesheets');
    }

    /**
     * Test building a production collection with the force parameter
     */
    public function testBuildingProductionCollectionWithForce()
    {
        $this->collection->shouldReceive('getAssetsWithoutRaw')->once()->with('stylesheets')->andReturn(new Illuminate\Support\Collection(array(
            $asset = m::mock('Basset\Asset')
        )));
        $asset->shouldReceive('build')->once()->andReturn('body { }');

        $this->collection->shouldReceive('getIdentifier')->once()->andReturn('foo');
        $this->collection->shouldReceive('getExtension')->once()->with('stylesheets')->andReturn('css');

        $fingerprint = 'foo-'.md5('body { }').'.css';

        $this->manifest->shouldReceive('make')->once()->with('foo')->andReturn($entry = m::mock('Basset\Manifest\Entry'));
        $entry->shouldReceive('getProductionFingerprint')->with('stylesheets')->andReturn($fingerprint);
        $entry->shouldReceive('setProductionFingerprint')->with('stylesheets', $fingerprint);

        $this->files->shouldReceive('put')->once()->with('foo/'.$fingerprint, 'body { }');

        $this->builder->setForce(true);
        $this->builder->buildAsProduction($this->collection, 'stylesheets');
    }

    /**
     * Test building production collection using gzip
     */
    public function testBuildingProductionCollectionWithGzip()
    {
        $this->collection->shouldReceive('getAssetsWithoutRaw')->once()->with('stylesheets')->andReturn(new Illuminate\Support\Collection(array(
            $asset = m::mock('Basset\Asset')
        )));
        $asset->shouldReceive('build')->once()->andReturn('body { }');

        $this->collection->shouldReceive('getIdentifier')->once()->andReturn('foo');
        $this->collection->shouldReceive('getExtension')->once()->with('stylesheets')->andReturn('css');

        $fingerprint = 'foo-'.md5('body { }').'.css';

        $this->manifest->shouldReceive('make')->once()->with('foo')->andReturn($entry = m::mock('Basset\Manifest\Entry'));
        $entry->shouldReceive('getProductionFingerprint')->with('stylesheets')->andReturn(null);
        $entry->shouldReceive('setProductionFingerprint')->with('stylesheets', $fingerprint);

        $this->files->shouldReceive('put')->once()->with('foo/'.$fingerprint, gzencode('body { }', 9));

        $this->builder->setGzip(true);
        $this->builder->buildAsProduction($this->collection, 'stylesheets');
    }

    /**
     * @expectedException Basset\Exceptions\BuildNotRequiredException
     */
    public function testBuildingDevelopmentCollectionWithNoAssetsThrowsBuildNotRequiredException()
    {
        $this->collection->shouldReceive('getAssetsWithoutRaw')->once()->with('stylesheets')->andReturn(new Illuminate\Support\Collection);
        $this->collection->shouldReceive('getIdentifier')->once()->andReturn('foo');

        $this->manifest->shouldReceive('make')->once()->with('foo')->andReturn($entry = m::mock('Basset\Manifest\Entry'));
        $entry->shouldReceive('hasDevelopmentAssets')->once()->with('stylesheets')->andReturn(false);
        $entry->shouldReceive('resetDevelopmentAssets')->once()->with('stylesheets');

        $this->builder->buildAsDevelopment($this->collection, 'stylesheets');
    }

    /**
     * @expectedException Basset\Exceptions\BuildNotRequiredException
     */
    public function testBuildingDevelopmentCollectionWithAssetsThatAreAlreadyBuiltThrowsBuildNotRequiredException()
    {
        $this->collection->shouldReceive('getAssetsWithoutRaw')->once()->with('stylesheets')->andReturn(new Illuminate\Support\Collection(array(
            $assets[] = m::mock('Basset\Asset'),
            $assets[] = m::mock('Basset\Asset')
        )));
        $this->collection->shouldReceive('getIdentifier')->once()->andReturn('foo');

        $assets[0]->shouldReceive('getRelativePath')->once()->andReturn('bar/baz.css');
        $assets[0]->shouldReceive('getBuildPath')->once()->andReturn('bar/baz-123.css');
        $assets[1]->shouldReceive('getRelativePath')->once()->andReturn('bar/qux.css');
        $assets[1]->shouldReceive('getBuildPath')->once()->andReturn('bar/qux-321.css');

        $this->manifest->shouldReceive('make')->once()->with('foo')->andReturn($entry = m::mock('Basset\Manifest\Entry'));
        $entry->shouldReceive('hasDevelopmentAsset')->once()->with($assets[0])->andReturn(true);
        $entry->shouldReceive('getDevelopmentAsset')->once()->with($assets[0])->andReturn('bar/baz-123.css');
        $entry->shouldReceive('hasDevelopmentAsset')->once()->with($assets[1])->andReturn(true);
        $entry->shouldReceive('getDevelopmentAsset')->once()->with($assets[1])->andReturn('bar/qux-321.css');

        $entry->shouldReceive('hasDevelopmentAssets')->once()->with('stylesheets')->andReturn(true);
        $entry->shouldReceive('getDevelopmentAssets')->once()->with('stylesheets')->andReturn(array(
            'bar/baz.css' => 'bar/baz-123.css',
            'bar/qux.css' => 'bar/qux-321.css'
        ));

        $this->builder->buildAsDevelopment($this->collection, 'stylesheets');
    }

    /**
     * Test building a development collection with no current manifest entry.
     */
    public function testBuildingDevelopmentCollectionWithNoCurrentManifestEntry()
    {
        $this->collection->shouldReceive('getAssetsWithoutRaw')->once()->with('stylesheets')->andReturn(new Illuminate\Support\Collection(array(
            $assets[] = m::mock('Basset\Asset'),
            $assets[] = m::mock('Basset\Asset')
        )));

        $group = 'stylesheets';
        $mockSupportCollection = m::mock('\Illuminate\Support\Collection');
        $mockSupportCollection->shouldReceive('getGroup')->andReturn($group);

        $this->collection->shouldReceive('getIdentifier')->once()->andReturn('foo');
        $this->collection->shouldReceive('getAssetsWithoutRaw')->with($group)->andReturn($mockSupportCollection);

        $entry = m::mock('Basset\Manifest\Entry');
        $entry->shouldReceive('addDevelopmentAsset')->andReturn(true);
        $entry->shouldReceive('hasDevelopmentAssets')->once()->with('stylesheets')->andReturn(false);
        $entry->shouldReceive('resetDevelopmentAssets')->once()->with('stylesheets');

        $assets[0]->shouldReceive('getBuildPath')->once()->andReturn('bar/baz-123.css');
        $assets[0]->shouldReceive('build')->once()->andReturn('body { }');

        $assets[1]->shouldReceive('getBuildPath')->once()->andReturn('bar/qux-321.css');
        $assets[1]->shouldReceive('build')->once()->andReturn('html { }');

        $entry->shouldReceive('addDevelopmentAsset')->once()->with($assets[0]);
        $entry->shouldReceive('addDevelopmentAsset')->once()->with($assets[1]);

        $this->manifest->shouldReceive('make')->once()->with('foo')->andReturn($entry);

        $this->files->shouldReceive('exists')->once()->with('foo/foo/bar')->andReturn(false);
        $this->files->shouldReceive('exists')->once()->with('foo/foo/bar')->andReturn(true);
        $this->files->shouldReceive('makeDirectory')->once()->with('foo/foo/bar', 0777, true);

        $this->files->shouldReceive('put')->once()->with('foo/foo/bar/baz-123.css', 'body { }');
        $this->files->shouldReceive('put')->once()->with('foo/foo/bar/qux-321.css', 'html { }');

        $this->builder->buildAsDevelopment($this->collection, $group);
    }

    /**
     * Test building development collection with no changes while using force parameter.
     */
    public function testBuildingDevelopmentCollectionWithNoChangesButWithForcing()
    {
        $this->collection->shouldReceive('getAssetsWithoutRaw')->once()->with('stylesheets')->andReturn(new Illuminate\Support\Collection(array(
            $assets[] = m::mock('Basset\Asset'),
            $assets[] = m::mock('Basset\Asset')
        )));
        $this->collection->shouldReceive('getIdentifier')->once()->andReturn('foo');

        $assets[0]->shouldReceive('getRelativePath')->once()->andReturn('bar/baz.css');
        $assets[0]->shouldReceive('getBuildPath')->once()->andReturn('bar/baz-123.css');
        $assets[0]->shouldReceive('build')->once()->andReturn('body { }');
        $assets[1]->shouldReceive('getRelativePath')->once()->andReturn('bar/qux.css');
        $assets[1]->shouldReceive('getBuildPath')->once()->andReturn('bar/qux-321.css');
        $assets[1]->shouldReceive('build')->once()->andReturn('html { }');

        $this->manifest->shouldReceive('make')->once()->with('foo')->andReturn($entry = m::mock('Basset\Manifest\Entry'));
        $entry->shouldReceive('hasDevelopmentAssets')->once()->with('stylesheets')->andReturn(true);
        $entry->shouldReceive('resetDevelopmentAssets')->once()->with('stylesheets');
        $entry->shouldReceive('getDevelopmentAssets')->once()->with('stylesheets')->andReturn(array(
            'bar/baz.css' => 'bar/baz-123.css',
            'bar/qux.css' => 'bar/qux-321.css'
        ));

        $entry->shouldReceive('addDevelopmentAsset')->once()->with($assets[0]);
        $entry->shouldReceive('addDevelopmentAsset')->once()->with($assets[1]);

        $this->files->shouldReceive('exists')->once()->with('foo/foo/bar')->andReturn(false);
        $this->files->shouldReceive('exists')->once()->with('foo/foo/bar')->andReturn(true);
        $this->files->shouldReceive('makeDirectory')->once()->with('foo/foo/bar', 0777, true);

        $this->files->shouldReceive('put')->once()->with('foo/foo/bar/baz-123.css', 'body { }');
        $this->files->shouldReceive('put')->once()->with('foo/foo/bar/qux-321.css', 'html { }');

        $this->builder->setForce(true);
        $this->builder->buildAsDevelopment($this->collection, 'stylesheets');
    }

    /**
     * Test building development collection with gzip.
     */
    public function testBuildingDevelopmentCollectionWithGzip()
    {
        $this->collection->shouldReceive('getAssetsWithoutRaw')->once()->with('stylesheets')->andReturn(new Illuminate\Support\Collection(array(
            $asset = m::mock('Basset\Asset')
        )));
        $this->collection->shouldReceive('getIdentifier')->once()->andReturn('foo');

        $asset->shouldReceive('getRelativePath')->once()->andReturn('bar/baz.css');
        $asset->shouldReceive('getBuildPath')->once()->andReturn('bar/baz-123.css');
        $asset->shouldReceive('build')->once()->andReturn('body { }');

        $this->manifest->shouldReceive('make')->once()->with('foo')->andReturn($entry = m::mock('Basset\Manifest\Entry'));
        $entry->shouldReceive('hasDevelopmentAssets')->once()->with('stylesheets')->andReturn(true);
        $entry->shouldReceive('getDevelopmentAssets')->once()->with('stylesheets')->andReturn(array('bar/baz.css' => 'bar/baz-123.css'));

        $entry->shouldReceive('hasDevelopmentAsset')->once()->with($asset)->andReturn(false);
        $entry->shouldReceive('addDevelopmentAsset')->once()->with($asset);

        $this->files->shouldReceive('exists')->once()->with('foo/foo/bar')->andReturn(true);

        $this->files->shouldReceive('put')->once()->with('foo/foo/bar/baz-123.css', gzencode('body { }', 9));

        $this->builder->setGzip(true);
        $this->builder->buildAsDevelopment($this->collection, 'stylesheets');
    }
}