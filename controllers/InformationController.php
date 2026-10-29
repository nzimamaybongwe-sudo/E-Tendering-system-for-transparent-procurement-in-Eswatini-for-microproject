<?php
/**
 * InformationController — manages educational content and guidance for SMEs.
 * Fulfills the "Tutorials and Guidelines" objective.
 */
class InformationController extends BaseController
{
    /**
     * Main SME Academy landing page.
     */
    public function index()
    {
        $this->view('guidance/index');
    }

    /**
     * Detailed document requirements and explanation.
     */
    public function requirements()
    {
        $this->view('guidance/requirements');
    }

    /**
     * Step-by-step guide on how to prepare and submit a bid.
     */
    public function biddingGuide()
    {
        $this->view('guidance/bidding_guide');
    }

    /**
     * User manual for new users navigating the system.
     */
    public function help()
    {
        $this->view('help');
    }
}
