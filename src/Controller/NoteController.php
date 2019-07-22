<?php

namespace App\Controller;

use Doctrine\DBAL\DBALException;
use Knp\Component\Pager\PaginatorInterface;
use App\Services\SphinxService;
use App\Form\NoteType;
use App\Entity\Sphinx\Note;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

class NoteController extends AbstractController
{
    protected $paginator;
    protected $sphinx;
    protected $request;
    protected $sm;  // sphinx manager

    function __construct(
        ContainerInterface $container,
        SphinxService $sphinx,
        PaginatorInterface $paginator
    )
    {
        $this->sphinx = $sphinx;
        $this->paginator = $paginator;
        $this->container = $container;
        $this->sm = $this->getDoctrine()
            ->getManager('sphinx');
    }

    /**
     * @Route("/", name="homepage")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $this->request = $request;
        return $this->render("note/list.html.twig", [
            'pagination' => $this->getNotesSearch(),
            'items' => $this->getNotesTopLevel()
        ]);
    }

    /**
     * @Route("/note/new/{parent_id}", name="note_new")
     * @param int $parent_id
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function new($parent_id = 0, Request $request)
    {
        $item = new Note();
        $item->setAttrParentId($parent_id);

        $form = $this->createForm(NoteType::class, $item);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $fieldsArray = $this->sphinx->insert(SphinxService::INDEX, $this->normalize($item));

            } catch (\Exception $e) {
                $this->addFlash('danger', $e->getMessage());
            }

            return $this->redirectToRoute('note_edit', ['id' => $fieldsArray['id']]);
        }
        return $this->render('note/edit.html.twig', [
            'form' => $form->createView(),
            'title' => "Создание заметки"
        ]);

    }

    /**
     * Просмотр заметки
     * @Route("/note/show/{id}", name="note_show")
     * @param int $id
     * @return Response
     * @throws DBALException
     * @throws ExceptionInterface
     */
    public function show($id = 0)
    {
        $item = $this->sm
            ->getRepository(Note::class)
            ->find($id);

        return $this->render("note/show.html.twig", [
            'item' => $item
        ]);
    }

    /**
     * @Route("/note/edit/{id}", name="note_edit")
     * @param int $id
     * @param Request $request
     * @return Response
     * @throws DBALException
     * @throws ExceptionInterface
     */
    public function edit($id = 0, Request $request)
    {
        $item = $this->sm
            ->getRepository(Note::class)
            ->find($id);

        $form = $this->createForm(NoteType::class, $item);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $item = $form->getData();

            try {

                $this->sphinx->replace(SphinxService::INDEX, $this->normalize($item));

            } catch (\Exception $e) {
                $this->addFlash('danger', $e->getMessage());
            }

            return $this->redirectToRoute('note_edit', ['id' => $id]);
        }
        return $this->render('note/edit.html.twig', [
            'form' => $form->createView(),
            'title' => "Редактирование заметки"
        ]);
    }

    public function normalize($entity = null)
    {
        $normalizer = new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter());
        $serializer = new Serializer([new PropertyNormalizer(), $normalizer]);

        return $serializer->normalize($entity);
    }

    /**
     * @return mixed
     */
    public function getNotesSearch()
    {
        $query = $this->sphinx->createNoteQuery();

        if ('' != $this->request->get('search', '')) {
            $query
                ->match(['title', 'content'],
                    $this->request->get('search', null)
                );

        }

        return $this->paginator
            ->paginate(
                $query->getResults(),
                $this->request->query->get('page', 1),
                20
            );

    }

    /**
     * Все заметки верхнего уровня
     * @return array
     */
    public function getNotesTopLevel()
    {
        return $this->sphinx
            ->createNoteQuery()
            ->where('attr_parent_id', 0)
            ->getResults();
    }

    /**
     * Удаление заметки
     * @Route("/note/remove/{id}", name="note_remove")
     * @param int $id
     * @return JsonResponse
     */
    public function remove($id = 0)
    {
        return new JsonResponse(
            $this->sphinx->remove(SphinxService::INDEX, $id));
    }

    /**
     * Сортировка nestable
     * @Route("/note/sort", name="note_sort")
     * @param Request $request
     * @return JsonResponse
     */
    public function sort(Request $request)
    {
        $items = json_decode($request->get('items'));
        $this->nodeSort($items);

        return new JsonResponse($items);
    }

    /**
     * Сортинг узла
     * @param $notes
     * @param int $parent_id
     */
    public function nodeSort($notes, $parent_id = 0)
    {
        $i = 0;
        foreach ($notes as $item) {
            if (isset($item->children) && count($item->children) > 0) {
                $this->nodeSort($item->children, $item->id);
            }

            $this->sphinx->update(SphinxService::INDEX, [
                'id' => $item->id,
                'attr_parent_id' => $parent_id,
                'attr_order_id' => $i
            ]);

            $i++;
        }
    }

    /**
     * Выводит дочерние узлы узла id
     * @Route("/note/children/{id}", name="note_children")
     * @param int $id
     * @return Response
     */
    public function children($id = 0)
    {
        $query = $this->sphinx
            ->createNoteQuery()
            ->where('attr_parent_id', $id);

        return $this->render('note/nestable/items.html.twig', ['items' => $query->getResults()]);
    }

    /**
     * @Route("/test", name="test")
     */
    public function test()
    {
        $em = $this->getDoctrine()->getManager("sphinx");

        $stmt = $em->getConnection()->prepare("SELECT * FROM note_1");
        $stmt->execute();
        dd($stmt->fetchAll());
    }
}