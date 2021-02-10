<?php
/*
    SiteController.php
    The route controller for the main site.
*/

namespace AppBundle\Controller;

use AppBundle\Entity\Auction;
use AppBundle\Entity\Bid;
use AppBundle\Entity\Bounty;
use AppBundle\Entity\Item;
use AppBundle\Entity\News;
use AppBundle\Entity\Order;
use AppBundle\Entity\Raid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class SiteController extends Controller
{
    /* USER SECTION */

    /**
     * @Route("/", name="home")
     */
    public function indexAction(Request $request)
    {
        $user = $this->getUser();
        $auctions = $this->getDoctrine()->getRepository("AppBundle:Auction")->findBy(["open" => true]);
        $bounties = $this->getDoctrine()->getRepository("AppBundle:Bounty")->findBy(["active" => true]);
        $news = $this->getDoctrine()->getRepository("AppBundle:News")->find(2);
        $items = $this->getDoctrine()->getRepository("AppBundle:Item")->createQueryBuilder('i')
            ->where('i.count > :count')
            ->setParameter('count', 0)
            ->getQuery()
            ->getResult();
        $orders = $this->getDoctrine()->getRepository("AppBundle:Order")->findBy(["buyer" => $user, "delivered" => false]);

        $bids = [];
        foreach ($auctions as $auction)
        {
            /** @var Auction $auction */
            $bids[$auction->getId()] = $auction->getUserBid($user);
        }

        return $this->render("home.html.twig", [
            "user" => $user,
            "auctions" => $auctions,
            "bids" => $bids,
            "bounties" => $bounties,
            "news" => $news,
            "items" => $items,
            "orders" => $orders
        ]);
    }

    /**
     * @Route("/bid/{auction_id}", name="place_bid")
     */
    public function bidAction(Request $request, $auction_id)
    {
        $em = $this->getDoctrine()->getManager();
        $bidAmount = $request->get("bid");
        $bidder = $this->getUser();
        if ($bidder->getPoints() < $bidAmount) return $this->redirectToRoute("home"); # Bail out

        /** @var Auction $auction */
        $auction = $em->getRepository("AppBundle:Auction")->find($auction_id);
        $bid = new Bid();
        $bid->setBid($bidAmount);
        $bid->setBidder($bidder);
        if ($auction->addBid($bid))
        {
            $bid->setAuction($auction);
            $em->persist($bid);
            $em->flush();
        }
        return $this->redirectToRoute("home");
    }

    /**
     * @Route("/bid/undo/{bid_id}", name="undo_bid")
     */
    public function undoBidAction(Request $request, $bid_id)
    {
        $em = $this->getDoctrine()->getManager();
        $bid = $em->getRepository("AppBundle:Bid")->find($bid_id);
        if ($bid->getAuction()->isOpen() and $bid->getBidder()->getId() == $this->getUser()->getId())
        {
            $em->remove($bid);
            $em->flush();
        }
        return $this->redirectToRoute("home");
    }

    /**
     * @Route("/mains", name="edit_mains")
     */
    public function editMainsAction(Request $request)
    {
        $jobs = ["WHM", "BLM", "RDM", "WAR", "THF", "MNK", "BRD", "BST", "DRK", "SMN", "NIN", "SAM", "RNG", "DRG", "PLD", "BLU", "COR", "PUP", "DNC", "SCH"];
        $user = $this->getUser();
        return $this->render("mains.html.twig", ["user" => $user, "jobs" => $jobs]);
    }

    /**
     * @Route("/mains/update", name="update_mains")
     */
    public function updateMainsAction(Request $request)
    {
        $mainJob = $request->get("main_job");
        $subJob = $request->get("sub_job");
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        $user->updateJobs($mainJob, $subJob);
        $em->flush();
        return $this->redirectToRoute("edit_mains");
    }

    /**
     * @Route("/roster", name="view_roster")
     */
    public function viewRosterAction(Request $request)
    {
        $jobs = ["WHM" => 0, "BLM" => 0, "RDM" => 0, "WAR" => 0, "THF" => 0, "MNK" => 0, "BRD" => 0, "BST" => 0, "DRK" => 0, "SMN" => 0, "NIN" => 0, "SAM" => 0, "RNG" => 0, "DRG" => 0, "PLD" => 0, "BLU" => 0, "COR" => 0, "PUP" => 0, "DNC" => 0, "SCH" => 0];
        $sort = ['username' => 'ASC'];
        if ($this->getUser()->hasRole('ROLE_AUCTION_ADMIN') or $this->getUser()->hasRole('ROLE_SUPER_ADMIN')) $sort = ['points' => 'DESC'];
        $users = $this->getDoctrine()->getRepository("AppBundle:User")->findBy(["visible" => 1], $sort);
        foreach ($users as $user)
        {
            if ($user->getMainJob() <> null) $jobs[$user->getMainJob()]++;
            if ($user->getSubJob() <> null) $jobs[$user->getSubJob()]++;
        }
        arsort($jobs);
        return $this->render("roster.html.twig", ["users" => $users, "jobs" => $jobs]);
    }

    /**
     * @Route("/info", name="view_info")
     */
    public function viewInfoAction(Request $request)
    {
        $news = $this->getDoctrine()->getRepository("AppBundle:News")->findBy([], ['id' => 'ASC']);
        return $this->render("info.html.twig", ['news' => $news]);
    }

    /**
     * @Route("/order/{item_id}", name="place_order")
     */
    public function placeOrderAction(Request $request, $item_id)
    {
        $em = $this->getDoctrine()->getManager();
        $item = $em->getRepository("AppBundle:Item")->find($item_id);
        $order = new Order();
        $order->setBuyer($this->getUser());
        $order->setItem($item);
        if ($order->fundOrder())
        {
            $em->persist($order);
            $em->flush();
        }
        return $this->redirectToRoute("home");
    }

    /**
     * @Route("/order/cancel/{order_id}", name="cancel_order")
     */
    public function cancelOrderAction(Request $request, $order_id)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $order = $em->getRepository("AppBundle:Order")->find($order_id);
        if ($order->getBuyer() == $user)
        {
            $order->refundOrder();
            $em->remove($order);
            $em->flush();
        }
        return $this->redirectToRoute("home");
    }

   /**
     * @Route("/store/cancel/{order_id}", name="refund_order")
     */
    public function refundOrderAction(Request $request, $order_id)
    {
        $em = $this->getDoctrine()->getManager();
        $order = $em->getRepository("AppBundle:Order")->find($order_id);
        $order->refundOrder();
        $em->remove($order);
        $em->flush();
        return $this->redirectToRoute("store_manager");
    }

    /* STORE SECTION */

    /**
     * @Route("/store", name="store_manager")
     */
    public function storeManagerAction(Request $request)
    {
        $items = $this->getDoctrine()->getRepository("AppBundle:Item")->findAll();
        $orders = $this->getDoctrine()->getRepository("AppBundle:Order")->findBy(['delivered' => false], ['id' => 'ASC']);
        return $this->render("items.html.twig", ['items' => $items, 'orders' => $orders]);
    }

    /**
     * @Route("/store/create", name="create_item")
     */
    public function createItemAction(Request $request)
    {
        return $this->render("item_create.html.twig");
    }

    /**
     * @Route("/store/init", name="init_item")
     */
    public function initItemAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $item = new Item();
        $item->setItemName($request->get("item_name"));
        $item->setPrice($request->get("price"));
        $item->setCount($request->get("count"));
        $em->persist($item);
        $em->flush();
        return $this->redirectToRoute("store_manager");
    }

    /**
     * @Route("/store/edit/{item_id}", name="edit_item")
     */
    public function editItemAction(Request $request, $item_id)
    {
        $item = $this->getDoctrine()->getRepository("AppBundle:Item")->find($item_id);
        return $this->render("item_edit.html.twig", ["item" => $item]);
    }

    /**
     * @Route("/store/update/{item_id}", name="update_item")
     */
    public function updateItemAction(Request $request, $item_id)
    {
        $em = $this->getDoctrine()->getManager();
        $item = $em->getRepository("AppBundle:Item")->find($item_id);
        $item->setItemName($request->get("item_name"));
        $item->setPrice($request->get("price"));
        $item->setCount($request->get("count"));
        $em->flush();
        return $this->redirectToRoute("store_manager");
    }

    /**
     * @Route("/store/deliver/{order_id}", name="deliver_order")
     */
    public function deliverOrderAction(Request $request, $order_id)
    {
        $em = $this->getDoctrine()->getManager();
        $order = $em->getRepository("AppBundle:Order")->find($order_id);
        $order->deliverOrder();
        $em->flush();
        return $this->redirectToRoute("store_manager");
    }

    /* RAID SECTION */

    /**
     * @Route("/raid", name="raid_manager")
     */
    public function raidManagerAction(Request $request)
    {
        $raids = $this->getDoctrine()->getRepository("AppBundle:Raid")->findBy([], ["raidDate" => "DESC"]);
        return $this->render("raids.html.twig", ["raids" => $raids]);
    }

    /**
     * @Route("/raid/create", name="create_raid")
     */
    public function createRaidAction(Request $request)
    {
        return $this->render("raid_create.html.twig");
    }

    /**
     * @Route("/raid/init", name="init_raid")
     */
    public function initRaidAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $raid = new Raid();
        $raid->setRaidTarget($request->request->get("raid_target"));
        $raid->setRaidDate($request->request->get("raid_date"));
        $em->persist($raid);
        $em->flush();
        return $this->redirectToRoute("raid_manager");
    }

    /**
     * @Route("/raid/attendance/{raid_id}/{focus}", name="manage_attendance")
     */
    public function manageAttendanceAction(Request $request, $raid_id, $focus = "on_time")
    {
        $raid = $this->getDoctrine()->getRepository("AppBundle:Raid")->find($raid_id);
        return $this->render("raid_attendance.html.twig", ["raid" => $raid, "focus" => $focus]);
    }

    /**
     * @Route("/raid/attendance/add/{type}/{raid_id}", name="add_attendee")
     */
    public function addAttendeeAction(Request $request, $type, $raid_id)
    {
        $em = $this->getDoctrine()->getManager();
        if ($type == "on_time") $character = $request->get("on_time_attendance");
        if ($type == "late") $character = $request->get("late_attendance");
        $user = $em->getRepository("AppBundle:User")->findOneBy(["username" => $character]);
        $raid = $em->getRepository("AppBundle:Raid")->find($raid_id);
        if ($type == "on_time") $raid->addOnTimeAttendee($user);
        if ($type == "late") $raid->addLateAttendee($user);
        $em->flush();
        return $this->redirectToRoute("manage_attendance", ["raid_id" => $raid_id, "focus" => $type]);
    }

    /**
     * @Route("/raid/attendance/remove/{type}/{raid_id}/{user_id}", name="remove_attendee")
     */
    public function removeOnTimeAttendeeAction(Request $request, $raid_id, $user_id, $type)
    {
        $em = $this->getDoctrine()->getManager();
        $raid = $em->getRepository("AppBundle:Raid")->find($raid_id);
        $user = $em->getRepository("AppBundle:User")->find($user_id);
        if ($type == "on_time") $raid->removeOnTimeAttendee($user);
        if ($type == "late") $raid->removeLateAttendee($user);
        $em->flush();
        return $this->redirectToRoute("manage_attendance", ["raid_id" => $raid_id]);
    }

    /**
     * @Route("/raid/edit/{raid_id}", name="edit_raid")
     */
    public function editRaidAction(Request $request, $raid_id)
    {
        $em = $this->getDoctrine()->getManager();
        $raid = $em->getRepository("AppBundle:Raid")->find($raid_id);
        return $this->render("raid_edit.html.twig", ["raid" => $raid]);
    }

    /**
     * @Route("/raid/update/{raid_id}", name="update_raid")
     */
    public function updateRaidAction(Request $request, $raid_id)
    {
        $em = $this->getDoctrine()->getManager();
        $raid = $em->getRepository("AppBundle:Raid")->find($raid_id);
        $raid->setRaidTarget($request->get("raid_target"));
        $raid->setRaidDate($request->get("raid_date"));
        $em->flush();
        return $this->redirectToRoute("raid_manager");
    }

    /* AUCTION SECTION */

    /**
     * @Route("/auction", name="auction_manager")
     */
    public function auctionManagerAction(Request $request)
    {
        $auctions = $this->getDoctrine()->getRepository("AppBundle:Auction")->findBy([], ['open' => "DESC"]);
        return $this->render("auctions.html.twig", ["auctions" => $auctions]);
    }

    /**
     * @Route("/auction/create", name="create_auction")
     */
    public function createAuctionAction(Request $request)
    {
        return $this->render("auction_create.html.twig");
    }

    /**
     * @Route("/auction/edit/{auction_id}", name="edit_auction")
     */
    public function editAuctionAction(Request $request, $auction_id)
    {
        $em = $this->getDoctrine()->getManager();
        $auction = $em->getRepository("AppBundle:Auction")->find($auction_id);
        return $this->render("auction_edit.html.twig", ["auction" => $auction]);
    }

    /**
     * @Route("/auction/update/{auction_id}", name="update_auction")
     */
    public function updateAuctionAction(Request $request, $auction_id)
    {
        $em = $this->getDoctrine()->getManager();
        $auction = $em->getRepository("AppBundle:Auction")->find($auction_id);
        $auction->setItemName($request->get("item_name"));
        $auction->setItemReq($request->get("item_req"));
        $auction->setMinBid($request->get("min_bid"));
        $em->flush();
        return $this->redirectToRoute("auction_manager");
    }

    /**
     * @Route("/auction/init", name="init_auction")
     */
    public function initAuctionAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $auction = new Auction();
        $auction->setItemName($request->request->get("item_name"));
        $auction->setItemReq($request->request->get("item_req"));
        $auction->setMinBid($request->request->get("min_bid"));
        $em->persist($auction);
        $em->flush();
        return $this->redirectToRoute("auction_manager");
    }

    /**
     * @Route("/auction/history/{auction_id}", name="bid_history")
     */
    public function bidHistoryAction(Request $request, $auction_id)
    {
        $auction = $this->getDoctrine()->getRepository("AppBundle:Auction")->find($auction_id);
        $bids = $this->getDoctrine()->getRepository("AppBundle:Bid")->findBy(['auction' => $auction], ['bid' => "DESC"]);
        return $this->render("bid_history.html.twig", ["auction" => $auction, "bids" => $bids]);
    }

    /**
     * @Route("/auction/end/{auction_id}", name="end_auction")
     */
    public function endAuctionAction(Request $request, $auction_id)
    {
        $em = $this->getDoctrine()->getManager();
        $auction = $em->getRepository("AppBundle:Auction")->find($auction_id);
        $auction->closeAuction();
        $em->flush();
        return $this->redirectToRoute("auction_manager");
    }

    /**
     * @Route("/auction/make_winning_bid/{bid_id}", name="make_winning_bid")
     */
    public function makeWinningBidAction(Request $request, $bid_id)
    {
        $em = $this->getDoctrine()->getManager();
        $bid = $em->getRepository("AppBundle:Bid")->find($bid_id);
        $bid->getAuction()->makeWinningBid($bid);
        $em->flush();
        return $this->redirectToRoute("bid_history", ["auction_id" => $bid->getAuction()->getId()]);
    }
    
    /* NEWS SECTION */

    /**
     * @Route("/news", name="news_manager")
     */
    public function newsManagerAction(Request $request)
    {
        $news = $this->getDoctrine()->getRepository("AppBundle:News")->findBy([], ["date" => "DESC"]);
        return $this->render("news.html.twig", ["news" => $news]);
    }

    /**
     * @Route("/news/create", name="create_news")
     */
    public function createNewsAction(Request $request)
    {
        return $this->render("news_create.html.twig");
    }

    /**
     * @Route("/news/init", name="init_news")
     */
    public function initNewsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $news = new News();
        $news->setPoster($this->getUser());
        $news->setTitle($request->get("title"));
        $news->setContent($request->get("content"));
        $em->persist($news);
        $em->flush();
        return $this->redirectToRoute("news_manager");
    }

    /**
     * @Route("/news/update/{news_id}", name="update_news")
     */
    public function updateNewsAction(Request $request, $news_id)
    {
        $em = $this->getDoctrine()->getManager();
        $news = $em->getRepository("AppBundle:News")->find($news_id);
        $news->setPoster($this->getUser());
        $news->setTitle($request->get("title"));
        $news->setContent($request->get("content"));
        $em->flush();
        return $this->redirectToRoute("news_manager");
    }

    /**
     * @Route("/news/edit/{news_id}", name="edit_news")
     */
    public function editNewsAction(Request $request, $news_id)
    {
        $news = $this->getDoctrine()->getRepository("AppBundle:News")->find($news_id);
        return $this->render("news_edit.html.twig", ['news' => $news]);
    }

    /* BOUNTY SECTION */

    /**
     * @Route("/bounty", name="bounty_manager")
     */
    public function bountyManagerAction(Request $request)
    {
        $bounties = $this->getDoctrine()->getManager()->getRepository("AppBundle:Bounty")->findAll();
        return $this->render("bounties.html.twig", ["bounties" => $bounties]);
    }

    /**
     * @Route("/bounty/add", name="add_bounty")
     */
    public function addBounty(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $bounty = new Bounty();
        $bounty->setTarget($request->get("target"));
        $bounty->setPoints($request->get("points"));
        $em->persist($bounty);
        $em->flush();
        return $this->redirectToRoute("bounty_manager");
    }

    /**
     * @Route("/bounty/toggle/{bounty_id}", name="toggle_bounty")
     */
    public function toggleBounty(Request $request, $bounty_id)
    {
        $em = $this->getDoctrine()->getManager();
        $bounty = $em->getRepository("AppBundle:Bounty")->find($bounty_id);
        $bounty->toggleActive();
        $em->flush();
        return $this->redirectToRoute("bounty_manager");
    }

    /* HELPER FUNCTIONS */

    /**
     * @Route("/autocomplete", name="user_autocomplete")
     */
    public function autocompleteAction(Request $request)
    {
        $names = array();
        $term = trim(strip_tags($request->get('term')));

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('AppBundle:User')->createQueryBuilder('u')
           ->where('u.username LIKE :name')
           ->setParameter('name', $term.'%')
           ->getQuery()
           ->getResult();

        foreach ($entities as $entity)
        {
            $names[] = $entity->getUsername();
        }

        $response = new JsonResponse();
        $response->setData($names);

        return $response;
    }
}
