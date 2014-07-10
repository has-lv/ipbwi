<?php
	/**
	 * @author			Matthias Reuter
	 * @package			rating
	 * @copyright		2007-2013 Matthias Reuter
	 * @link			n.A.
	 * @since			3.4
	 * @license			http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License
	 */

    namespace IPBWI;

	class ipbwi_rating extends ipbwi {
		private $ipbwi			= null;
		/**
		 * @desc			Loads and checks different vars when class is initiating
		 * @author			Matthias Reuter
		 * @since			2.0
		 * @ignore
		 */
		public function __construct($ipbwi){
			// loads common classes
			$this->ipbwi = $ipbwi;
		}
		
		/**
		 * Retuns an array for use in a join statement
		 *
		 * @access	public
		 * @param  	string		$type		Type of content, ex; Post
		 * @param	integer		$type_id	ID of the type, ex: pid
		 * @param  	string		[$app]		App for this content, by default the current application
		 * @return	array
		 */
		public function getTotalRatingJoin( $type, $type_id, $app='' )
		{
			return $this->ipbwi->ips_wrapper->rep->getTotalRatingJoin( $type, $type_id, $app );
		}
		
		/**
		 * Has this member rated this item?
		 * @param 	array $data (app, id, type, memberId )
		 * @return	boolean
		 */
		public function getCurrentMemberRating( $data )
		{
			return $this->ipbwi->ips_wrapper->rep->getCurrentMemberRating( $data );
		}
		
		/**
		 * Has this member rated this item?
		 * @param 	array $data (app, id, type, memberId )
		 * @return	int
		 */
		public function getCurrentRating( $data )
		{
			return $this->ipbwi->ips_wrapper->rep->getCurrentRating( $data );
		}
		
		/**
		 * Retuns an array for use in a join statement
		 *
		 * @access	public
		 * @param	string		$type		Type of content, ex; Post
		 * @param	integer		$type_id	ID of the type, ex: pid
		 * @param	string		[$app]		App for this content, by default the current application
		 * @return	array
		 */	
		public function getUserHasRatedJoin( $type, $type_id, $app='' )
		{
			return $this->ipbwi->ips_wrapper->rep->getUserHasRatedJoin( $type, $type_id, $app );
		}
		
		/**
		 * Adds a rating to the index and updates caches
		 *
		 * @access	public
		 * @param	string		$type		Type of content, ex; Post
		 * @param	integer		$type_id	ID of the type, ex: pid
		 * @param	integer		$rating		Either 1 or -1
		 * @param	string		$message	Message associated with this rating
		 * @param	integer		$member_id	Id of the owner of the content being rated
		 * @param	string		[$app]		App for this content, by default the current application
		 * @todo 	[Future] Move forum notifications to an onRep memberSync callback
		 * @return	bool
		 */
		public function addRate( $type, $type_id, $rating, $message='', $member_id=0, $app='' )
		{
			return $this->ipbwi->ips_wrapper->rep->addRate( $type, $type_id, $rating, $message, $member_id, $app );
		}
		
		/**
		 * Returns an array of reputation information based on the points passed in
		 *
		 * @access	public
		 * @param	integer		$points		Number of points to base the repuation information on
		 * @return	array 					'text' and 'image'
		 */
		public function getReputation( $points )
		{
			return $this->ipbwi->ips_wrapper->rep->getReputation( $points );
		}
		
		/**
		 * Get 'like' formatted for this item
		 * 
		 * @param	array	Data array 	( id, type, app )
		 */
		public function getLikeFormatted( $item )
		{
			return $this->ipbwi->ips_wrapper->rep->getLikeFormatted( $item );
		}
		
		/**
		 * Gets 'like' data for this item
		 *
		 * @param	array	( id, type, app )
		 * @return	array
		 *
		 */
		public function getRepPoints( $data )
		{
			return $this->ipbwi->ips_wrapper->rep->getRepPoints( $data );
		}
		
		/**
		 * Gets 'like' data for this item
		 * 
		 * @param	array	( id, type, app )
		 * @return	array
		 * 
		 */
		public function getLikeData( $data )
		{
			return $this->ipbwi->ips_wrapper->rep->getLikeData( $data );
		}
		
		/**
		 * Get the like data from the DB (no cache)
		 * 
		 * @param	array		$data		Like data ( id, type, app )
		 * @return	@e array	Cache data
		 */
		public function getLikeRawData( $data )
		{
			return $this->ipbwi->ips_wrapper->rep->getLikeRawData( $data );
		}
		
		/**
		 * Formats the Bob, Bill, Joe and 2038 Others Hate You
		 * 
		 * @param	array	$data
		 * @param	array	$item	Data (id, type, app)
		 * @return	string
		 */
		public function formatLikeNameString( array $data, array $item )
		{
			return $this->ipbwi->ips_wrapper->rep->formatLikeNameString($data, $item );
		}
		
		/**
		 * Get data based on a relationship ID
		 *
		 * @param	array 	$data (id, type, app)
		 * @return	mixed	Array of like data OR null
		 */
		public function getDataByRelationshipId( $data )
		{
			return $this->ipbwi->ips_wrapper->rep->getDataByRelationshipId( $data );
		}
		
		/**
		 * Handles updating and creating new caches
		 *
		 * @access	private
		 * @param	string	$app		App for this content
		 * @param	string	$type		Type of content, ex; Post
		 * @param	integer	$type_id	ID of the type, ex: pid
		 * @return	@e void
		 */
		public function updateCache( $app, $type, $type_id )
		{		
			return $this->ipbwi->ips_wrapper->rep->updateCache( $app, $type, $type_id );
		}
		
		/**
		 * Is this in like mode?
		 */
		public function isLikeMode()
		{
			return $this->ipbwi->ips_wrapper->rep->isLikeMode();
		}
		
		public function likeItemExists( $app, $type, $type_id )
		{
			return $this->ipbwi->ips_wrapper->rep->likeItemExists( $app, $type, $type_id );
		}
	}
?>