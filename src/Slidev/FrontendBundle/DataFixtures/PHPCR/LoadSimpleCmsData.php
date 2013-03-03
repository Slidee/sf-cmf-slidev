<?php

namespace Slidev\FrontendBundle\DataFixtures\PHPCR;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Yaml\Parser;

use PHPCR\Util\NodeHelper;

use Symfony\Cmf\Bundle\SimpleCmsBundle\DataFixtures\LoadCmsData;
use Symfony\Cmf\Bundle\SimpleCmsBundle\Document\MultilangRedirectRoute;
use Symfony\Cmf\Bundle\SimpleCmsBundle\Document\MultilangRoute;
use Symfony\Cmf\Bundle\MenuBundle\Document\MultilangMenuNode;


//use Symfony\Cmf\Bundle\BlockBundle\Document\SimpleBlock;
use Symfony\Cmf\Bundle\ContentBundle\Document\StaticContent;

class LoadSimpleCmsData extends LoadCmsData
{
    private $yaml;

    public function getOrder()
    {
        return 5;
    }

    protected function getData()
    {
        return $this->yaml->parse(file_get_contents(__DIR__.'/../../Resources/data/page.yml'));
    }

    public function load(ObjectManager $dm)
    {
        $this->yaml = new Parser();

        parent::load($dm);

        $data = $this->yaml->parse(file_get_contents(__DIR__ . '/../../Resources/data/external.yml'));

        $basepath = $this->container->getParameter('symfony_cmf_simple_cms.basepath');
        $home = $dm->find(null, $basepath);

        $route = new MultilangRoute();
        $route->setPosition($home, 'dynamic');
        $route->setDefault('_controller', 'SlidevFrontendBundle:Main:dynamic');

        $dm->persist($route);

        foreach ($data['static'] as $name => $overview) {
            $menuItem = new MultilangMenuNode();
            $menuItem->setName($name);
            $menuItem->setParent($home);
            if (!empty($overview['route'])) {
                if (!empty($overview['uri'])) {
                    $route = new MultilangRedirectRoute();
                    $route->setPosition($home, $overview['route']);
                    $route->setUri($overview['uri']);
                    $dm->persist($route);
                } else {
                    $route = $dm->find(null, $basepath.'/'.$overview['route']);
                }
                $menuItem->setRoute($route->getId());
            } else {
                $menuItem->setUri($overview['uri']);
            }
            
            $dm->persist($menuItem);
            foreach ($overview['label'] as $locale => $label) {
                $menuItem->setLabel($label);
                if ($locale) {
                    $dm->bindTranslation($menuItem, $locale);
                }
            }
        }

        $dm->flush();
        
        
        ///
        ///Override to create static blocs samples
        ///
        
        $manager = $dm;
        // Get the base path name to use from the configuration
        $session = $manager->getPhpcrSession();
        $basepath = $this->container->getParameter('symfony_cmf_content.static_basepath');

        // Create the path in the repository
        NodeHelper::createPath($session, $basepath);

        // Create a new document using StaticContent from the CMF ContentBundle
        $document = new StaticContent();
        $document->setPath($basepath . '/blocks');
        $manager->persist($document);

        // Create a new SimpleBlock (see http://symfony.com/doc/master/cmf/bundles/block.html#block-types)
        /*$myBlock = new SimpleBlock();
        $myBlock->setParentDocument($document);
        $myBlock->setName('testBlock');
        $myBlock->setTitle('CMF BlockBundle and ContentBundle');
        $myBlock->setContent('Block from CMF BlockBundle, parent from CMF ContentBundle (StaticContent).');
        $manager->persist($myBlock);*/

        // Commit $document and $block to the database
        $manager->flush();
       
    }
}
