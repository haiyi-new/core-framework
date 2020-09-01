<?php
namespace Webkul\UVDesk\CoreFrameworkBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Webkul\UVDesk\CoreFrameworkBundle\Services\UserService;
use Symfony\Component\Translation\TranslatorInterface;

class GroupXHR extends AbstractController
{
    private $userService;
    private $translator;

    public function __construct(UserService $userService, TranslatorInterface $translator)
    {
        $this->userService = $userService;
        $this->translator = $translator;
    }

    public function listGroupsXHR(Request $request)
    {
        if (!$this->userService->isAccessAuthorized('ROLE_AGENT_MANAGE_GROUP')) {
            return $this->redirect($this->generateUrl('helpdesk_member_dashboard'));
        }

        if (true === $request->isXmlHttpRequest()) {
            $paginationResponse = $this->getDoctrine()->getRepository('UVDeskCoreFrameworkBundle:SupportGroup')->getAllGroups($request->query, $this->container);

            return new Response(json_encode($paginationResponse), 200, ['Content-Type' => 'application/json']);
        }
        
        return new Response(json_encode([]), 404, ['Content-Type' => 'application/json']);
    }

    public function deleteGroupXHR($supportGroupId)
    {
        if(!$this->userService->isAccessAuthorized('ROLE_AGENT_MANAGE_GROUP')) {          
            return $this->redirect($this->generateUrl('helpdesk_member_dashboard'));
        }

        $request = $this->get('request_stack')->getCurrentRequest();
        if ($request->getMethod() == "DELETE") {
            $entityManager = $this->getDoctrine()->getManager();
            $supportGroup = $entityManager->getRepository('UVDeskCoreFrameworkBundle:SupportGroup')->findOneById($supportGroupId);

            if (!empty($supportGroup)) {
                $entityManager->remove($supportGroup);
                $entityManager->flush();
                
                return new Response(json_encode([
                    'alertClass' => 'success',
                    'alertMessage' => 'Support Group removed successfully.',
                ]), 200, ['Content-Type' => 'application/json']);
            }
        }
        
        return new Response(json_encode([]), 404, ['Content-Type' => 'application/json']);
    }
}
