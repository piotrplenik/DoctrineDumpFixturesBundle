<?php

/*
 * This file is part of the Doctrine Dump Fixtures Bundle
 *
 * (c) Piotr Plenik <piotr.plenik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TeamLab\Bundle\FixturesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use TeamLab\Bundle\FixturesBundle\Exception\CommandException;
use TeamLab\Bundle\FixturesBundle\Reflection\EntityProvider;

/**
 * Dump data fixtures from bundles.
 *
 * @author Piotr Plenik <piotr.plenik@gmail.com>
 */
class DoctrineDumpCommand extends ContainerAwareCommand
{
    public static $indent = '          ';

    protected function configure()
    {
        $this
                ->setName('doctrine:fixtures:dump')
                ->setDescription('Dump your database data to fixtures.')
        ;
    }

    protected function getBundleNamespace($className)
    {
        $bundles = $this->getContainer()->getParameter('kernel.bundles');

        foreach ($bundles as $bundle) {
            preg_match('/(.*)\\\\\w+/', $bundle, $bundleNameSpace);
            $bundleNameSpace = $bundleNameSpace[1];

            $pattern = '/'.str_replace('\\', '\\\\', $bundleNameSpace).'/';
            if (preg_match($pattern, $className)) {
                return $bundleNameSpace;
            }
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();
        $entities = $em->getConfiguration()->getMetadataDriverImpl()->getAllClassNames();

        if ($input->isInteractive()) {
            $helper = $this->getHelper('question');

            $question = new ConfirmationQuestion(
                'Careful, existing data fixtures will be override. Do you want to continue (y/N) ?',
                false
            );

            if (!$helper->ask($input, $output, $question)) {
                return;
            }
        }

        foreach ($entities as $entityName) {
            $provider = new EntityProvider($entityName);
            if (!$provider->valid()) {
                continue;
            }

            $fields = $provider->getDumpFields();

            if (count($fields) == 0) {
                $output->writeln(sprintf('<error>No fields for entity:</error> "<info>%s</info>".', $entityName));
                continue;
            }

            $output->writeln(sprintf('Generating dump file for entity "<info>%s</info>", fields: <info>%s</info>',
                $entityName, implode(', ', array_keys($fields))));

            try {
                $this->generateFixtures($provider, $entityName, $fields);
            } catch (CommandException $e) {
                $output->writeln($e->getMessage());
                exit(1);
            }
        }

        exit(0);
    }

    /**
     * Load Collection of Doctrine entities.
     *
     * @param $name
     * @param $entityName
     * @param $fields
     *
     * @return array
     *
     * @throws \Exception
     */
    protected function loadCollaction($name, $entityName, $fields)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $entityRepository = $em->getRepository($entityName);

        $meta = $em->getClassMetadata($entityName);
        $identifier = $meta->getSingleIdentifierFieldName();
        $identifierMethodName = 'get'.ucfirst($identifier);

        $collection = $entityRepository->findAll();

        $result = array();

        foreach ($collection as $item) {
            $element = array();

            foreach ($fields as $fieldName => $attributes) {
                $methodName = 'get'.ucfirst($fieldName);
                $fieldValue = $item->$methodName();

                $value = null;

                if (is_object($fieldValue)) {
                    if (get_class($fieldValue) === 'DateTime') {
                        $value = array('type' => 'DateTime', 'name' => $fieldValue->format('r'));
                    } elseif (array_key_exists('targetEntity', $attributes)) {
                        $targetClassName = $meta->associationMappings[$fieldName]['targetEntity'];
                        $targetEntityProvider = new EntityProvider($targetClassName);

                        $targetMethodName = 'get'.ucfirst($em->getClassMetadata($targetClassName)->identifier[0]);
                        $value = array(
                            'type' => 'reference',
                            'name' => $targetEntityProvider->getName().'_'.$fieldValue->$targetMethodName(),
                        );
                    } else {
                        throw new \Exception('aaa');
                    }
                } else {
                    $value = $fieldValue;
                }

                $element[$fieldName] = $value;
            }

            $result[$name.'_'.$item->$identifierMethodName()] = $element;
        }

        return $result;
    }

    /**
     * Render one Entity item.
     *
     * @param $em
     * @param $data
     * @param $attributes
     * @param $association
     *
     * @return array|mixed
     *
     * @throws \Exception
     */
    protected function loadItem($em, $data, $attributes, $association)
    {
        if (is_object($data)) {
            if (get_class($data) === 'DateTime') {
                return array('type' => 'DateTime', 'name' => $data->format('r'));
            }

            if (array_key_exists('targetEntity', $attributes)) {
                $targetClass = $em->getClassMetadata($attributes['targetEntity']);

//                var_dump($association);die();
                $className = get_class($data);

//                die($className);
                $entityProvider = new EntityProvider($className);

//                $meta = $em->getClassMetadata($className);
                $identifier = $targetClass->getSingleIdentifierFieldName();
                $identifierMethodName = 'get'.ucfirst($identifier);

                return array(
                    'type' => 'reference',
                    'name' => $entityProvider->getName().'_'.$data->$identifierMethodName(),
                );
            }

            throw new \Exception('Unknown data class: '.get_class($data));
        }

        if (!is_array($data)) {
            return str_replace(array("\r\n",   "\r", "\n"), '\n', htmlspecialchars($data, ENT_QUOTES));
        }

        return $data;
    }

    protected function exportData($array)
    {
        $data = var_export($array, true);

        $output = '';

        foreach (preg_split("/((\r?\n)|(\r\n?))/", $data) as $line) {
            $output .= self::$indent.$line.PHP_EOL;
        }

        return $output;
    }

    protected function generateFixtures(EntityProvider $provider, $entityName, $fields)
    {
        $bundleNamespace = $this->getBundleNamespace($entityName);

        $content = '<?php '."\n\n";
        $content .= 'namespace '.$bundleNamespace.'\DataFixtures\ORM;'."\n\n";
        $content .= 'use TeamLab\Bundle\FixturesBundle\AbstractFixture;'."\n";
        $content .= 'use Doctrine\Common\Persistence\ObjectManager;'."\n\n\n";

        $content .= 'class Load'.ucfirst($provider->getName()).'Data extends AbstractFixture
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->loadObjects($manager, \'' .$entityName.'\');
    }

    public function getData(ObjectManager $manager)
    {
        return
' .$this->exportData($this->loadCollaction($provider->getName(), $entityName, $fields)).'
        ;
    }

    public function getOrder()
    {
        return ' .$provider->getSequence().';
    }
}
';
        // create file
        $fixturesDir = $this->getContainer()->get('kernel')->getRootDir().
            DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.
            str_replace('\\', DIRECTORY_SEPARATOR, $bundleNamespace).DIRECTORY_SEPARATOR.
            'DataFixtures'.DIRECTORY_SEPARATOR.'ORM';

        if (!is_dir($fixturesDir)) {
            @mkdir($fixturesDir, 0777, true);
        }

        $fixturesFilename = $fixturesDir.DIRECTORY_SEPARATOR.'Load'.$provider->getClassName().'Data.php';

        file_put_contents($fixturesFilename, $content);
    }
}
