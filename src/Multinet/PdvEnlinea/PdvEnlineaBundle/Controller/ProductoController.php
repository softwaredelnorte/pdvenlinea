<?php

namespace Multinet\PdvEnlinea\PdvEnlineaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Multinet\PdvEnlinea\PdvEnlineaBundle\Entity\Producto;
use Multinet\PdvEnlinea\PdvEnlineaBundle\Form\ProductoType;

/**
 * Producto controller.
 *
 * @Route("/producto")
 */
class ProductoController extends Controller
{
    public function createFormUpload(){
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('producto_upload'))
            ->setMethod('POST')
            ->add('file', 'file', array('label' => 'Archivo','attr' => array('class' => 'form-control')))
            ->add('submit', 'submit', array('label' => 'Upload','attr' => array('class' => 'btn btn-success btn-sm')))
            ->getForm()
        ;
    }
    public function uploadAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $form = $this->createFormUpload();
        $form->handleRequest($request);
        $file = $form->get('file');
        $filename = $file->getData();
        $row = 3;
        if (($handle = fopen($filename, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 100, ";")) !== FALSE) {
                $num = count($data);
                $row++;
                foreach ($data as $key) {
                    $producto = new Producto();
                    $data = explode(",", $key);
                    $tipoMoneda = $em->getRepository('PdvBundle:TipoMoneda')->find($key[2]);
                    $producto->setIdTipoMoneda($tipoMoneda);
                    $producto->setNombre($key[6]);
                    $producto->setPreciocosto($key[7]);
                    $producto->setPrecioventa($key[8]);
                    $producto->setCreatedat(new \DateTime());
                    $producto->setUpdatedat(new \DateTime());
                    $producto->setStatus(1);
                    $producto->setStock($key[13]);
                    $em->persist($producto);
                    $em->flush();
                }
            }
            fclose($handle);
        }
    }

    /**
     * Lists all Producto entities.
     *
     * @Route("/", name="producto")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('PdvBundle:Producto')->findBy(array(
            'status' => 1
        ));
        $form = $this->createFormUpload();
        return array(
            'entities' => $entities,
            'form' => $form->createView()
        );
    }
    /**
     * Creates a new Producto entity.
     *
     * @Route("/", name="producto_create")
     * @Method("POST")
     * @Template("PdvBundle:Producto:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Producto();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('producto_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Producto entity.
     *
     * @param Producto $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Producto $entity)
    {
        $form = $this->createForm(new ProductoType(), $entity, array(
            'action' => $this->generateUrl('producto_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Producto entity.
     *
     * @Route("/new", name="producto_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Producto();
        $entity->setCreatedAt(new \DateTime());
        $entity->setUpdatedAt(new \DateTime());
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Producto entity.
     *
     * @Route("/{id}", name="producto_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PdvBundle:Producto')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Producto entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Producto entity.
     *
     * @Route("/{id}/edit", name="producto_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PdvBundle:Producto')->find($id);
        $entity->setUpdatedAt(new \DateTime());
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Producto entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Producto entity.
    *
    * @param Producto $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Producto $entity)
    {
        $form = $this->createForm(new ProductoType(), $entity, array(
            'action' => $this->generateUrl('producto_update', array('id' => $entity->getId())),
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Producto entity.
     *
     * @Route("/{id}", name="producto_update")
     * @Method("PUT")
     * @Template("PdvBundle:Producto:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PdvBundle:Producto')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Producto entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('producto_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Producto entity.
     *
     * @Route("/{id}", name="producto_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('PdvBundle:Producto')->find($id);
            $entity->setStatus(0);
            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Producto entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('producto'));
    }

    /**
     * Creates a form to delete a Producto entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('producto_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Eliminar Registro','attr' => array('class' => 'btn btn-danger btn-sm', 'onclick' => 'submitForm(this)', 'data-loading-text' => 'Eliminando Registro...')))
            ->getForm()
        ;
    }
}
