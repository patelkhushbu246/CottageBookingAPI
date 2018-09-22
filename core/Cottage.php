<?php
    include 'Helper.php';

    class Cottage {
        var $conn = null;
        var $helper = null;

        function __construct(){
            if($this->helper == null){
                $this->helper = new Helper();
            }

            if($this->conn == null){
                $this->conn = $this->helper->db();
            }
        }

        function test() {
            print_r($this->conn);
        }

        function get_list(){
            $query = "SELECT cot.*,cat.category FROM cottages cot INNER JOIN categories cat ON cot.category_id = cat.id";
            $res = mysqli_query($this->conn,$query);
            if(mysqli_num_rows($res) > 0){
                $list = [];
                while($result = mysqli_fetch_array($res,MYSQLI_ASSOC)){
                    $result['reviews'] = $this->_get_reviews($result['id']);
                    $list[] = $result;
                }
                $this->helper->create_response(true,"List Found",$list);
            }else{
                $this->helper->create_response(false,"List Not Found",null);
            }
        }

        /**
         * strict search in category id and lasy search in place
         * @param searchInput input value by user
         * @param field identifier
         *      category -> search by category
         *      place -> search by place
         */
        function search_by($searchInput,$field){
            $query = "SELECT * FROM cottages"; // all data (filter not applied) 
            if($field == 'category'){
                $query = "SELECT * FROM cottages where category_id='$searchInput'"; // search by category
            }else if($field == 'place'){
                $query = "SELECT * FROM cottages where place LIKE '$searchInput'"; // search by place (lasy search)
            }
            $res = mysqli_query($this->conn,$query);
            if(mysqli_num_rows($res) > 0){
                $list = [];
                while($result = mysqli_fetch_array($res,MYSQLI_ASSOC)){
                    $list[] = $result;
                }
                $this->helper->create_response(true,"Found",$list);
            }else{
                $this->helper->create_response(false,"No Results Found",null);
            }
        }

        // date format should be yyyy-mm-dd
        function add_review($data){
            $review = $data['review'];
            $cottage_id = $data['cottage_id'];
            $dateTime = new DateTime($data['date']); // use for fail safe 
            $date = $dateTime->format('Y-m-d');
            $query = "INSERT INTO reviews VALUES(null,'$review','$cottage_id','$date')";
            $res = mysqli_query($this->conn,$query);
            if($res > 0){
                $this->helper->create_response(true,"Review added",null);
            }else{
                $this->helper->create_response(true,"Sorry! try again later",null);
            }
        }
        
        private function _get_reviews($cottage_id){
            $reviewQuery = "SELECT review, date from reviews where cottage_id='$cottage_id'";
            $res = mysqli_query($this->conn,$reviewQuery);
            if(mysqli_num_rows($res) > 0) {
                $list = [];
                while ($result = mysqli_fetch_array($res,MYSQLI_ASSOC)){
                    $dateTime = new DateTime($result['date']); // change date format
                    $result['date'] = $dateTime->format('d-M-Y');
                    $list[] = $result;
                }
                return $list;
            }else{
                return [];
            }
        }
    }

    $cottage = new Cottage();
    $cottage->get_list();
    // $cottage->search_by("%ahmedabad%",'place');
    // $cottage->search_by("1",'category');
    // $cottage->add_review(array("review"=>"this is review for old rethel greens","cottage_id"=>1,"date"=>"01-05-2016"));
?>