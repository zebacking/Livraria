<?php

namespace LivrariaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use \LivrariaBundle\Entity\Cupom;
use \LivrariaBundle\Entity\CupomItem;
use \LivrariaBundle\Entity\Produtos;

/**
 * Description of CaixaController
 *
 * @author aluno
 */
class CaixaController extends Controller {

    /**
     * @Route("/caixa")
     * 
     */
    public function pdvAction(Request $request) {
        $cupomId = $request->getSession()->get('cupom-id', NULL);

        if ($cupomId === null) {
            $cupom = new Cupom();
            $cupom->setData(new \DateTime());
            $cupom->setValorTotal(0);
            $cupom->setVendedor(1);

            $em = $this->getDoctrine()->getManager();
            $em->persist($cupom);
            $em->flush();

            $request->getSession()->set('cupom-id', $cupom->getId());
        }
        return $this->render("LivrariaBundle:Caixa:pdv.html.twig");
    }

    /**
     * @Route("/caixa/carregar", name="pesquisar_produto")
     * @Method("POST")
     */
    public function carregarProdutoAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $codProd = $request->request->get('codigo');

        $cupomId = $request->getSession()->get('cupom-id');

        $produto = $em->getRepository('LivrariaBundle:Produtos')
                ->find($codProd);
        $cupom = $em->getRepository("LivrariaBundle:Cupom")
                ->find($cupomId);
        $quantItem = $em->getRepository("LivrariaBundle:CupomItem")
                ->findBy( array("cupomId"=> $cupomId) );

      
        
        if ($produto instanceof Produtos) {
            $novoItem = new CupomItem();
            $novoItem->setCupomId($cupom);
            $novoItem->setDescricaoItem($produto->getNome());
            $novoItem->setItemCod($codProd);
            $novoItem->setQuantidade(1);
            $novoItem->setValorUnitario($produto->getPreco());
            $novoItem->setOrdemItem(count($quantItem) + 1 );

            $em->persist($novoItem);
            $em->flush();

            $retorno["status"] = "ok";
            $retorno["produto"] = $produto;
        } else {
            $retorno["status"] = "erro";
            $retorno["mensagem"] = "Produto não encontrado";
        }
        return $this->json($retorno);
    }

    /**
     * @Route("/caixa/estorno/{item}")
     */
    public function estornarItemAction(Request $request, $item) {
        $cupomId = $request->getSession()->get('cupom-id');

        $em = $this->getDoctrine()->getManager();

        $itemOld = $em->getRepository('LivrariaBundle:CupomItem')
                ->findBy(array(
            'cupomId' => $cupomId,
            'ordemItem' => $item
        ));
        $itemEstorno = new CupomItem();
        $itemEstorno->setCupomId($cupomId);
        $itemEstorno->setDescricaoItem("Estorno do Item: $item");
        $itemEstorno->setItemCod(1001);
        $itemEstorno->setQuantidade(1);
        $itemEstorno->setValorUnitario($itemOld->getValorUnitario() * -1);

        $em->persist($itemEstorno);
        $em->flush();

        return $this->json('ok');
    }

    /**
     * @Route("/caixa/cancelar")
     */
    public function cancelarVendaAction(Request $request) {

        $cupomId = $request->getSession()->get('cupom-id');

        $em = $this->getDoctrine()->getManager();
        $cupom = $em->getRepository('LivrariaBundle:Cupom')->find($cupomId);

        $cupom->setStatus('CANCELADO');

        $em->persist($cupom);
        $em->flush();

        return $this->json('ok');
    }

    /**
     * @Route("/caixa/finalizar")
     */
    public function finalizarVendaAction(Request $request) {
        $cupomId = $request->getSession()->get('cupom-id');

        $em = $this->getDoctrine()->getManager();
        $cupom = $em->getRepository('LivrariaBundle:Cupom')->find($cupomId);

        $cupom->setStatus('FINALIZADO');

        $em->persist($cupom);
        $em->flush();

        // Baixar os itens do estoque
        // Fechar total da compra

        return $this->json('ok');
    }

    /**
     * @Route("/caixa/listar", name="listagem")
     */
    public function listarItensAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $itens = $em->getRepository("LivrariaBundle:CupomItem")
                ->findBy(array(
            "cupomId" => $request->getSession()->get('cupom-id')
        ));

        return $this->json($itens);
        
        
    }
}
