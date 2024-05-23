<?php

    //bom 등록/수정 페이지

    define('__CORE_TYPE__', 'view');
    include $_SERVER['DOCUMENT_ROOT'].'/function/core.php';

    //신규일 경우 초기값
    //품목sid는 null 
    $product_sid = null;
    $title = 'BOM등록 화면';
    $btn_text = '등록';
    $api_url = 'api_bom_insert';

    //품목sid, 자재sid, 자재수량 공백으로
    $product_sid = '';
    $material_sid = '';
    $bom_ea = '';
    $bom_note = '';

    $is_update = true;
    $query_result_product_name = array();
    $company_sid = false;
    $update_name = '';

    //품목sid 유효성 검사
    if(isset($_POST['product_sid']) === false){
        $is_update = false;
    }
    if($is_update === true && ($_POST['product_sid'] === '' || $_POST['product_sid'] === null)){
        $is_update = false;
    }

    //품목 sid가 있을 경우
    if ($is_update === true) {
        $update_sid = $_POST['product_sid'];
        //품목 sid를 조건으로 품목명, 회사코드 조회
        $select_sql = "SELECT product_name, company_sid from product_info WHERE sid='$update_sid'";

        $query_result_product_name = sql($select_sql);
        $query_result_product_name = select_process($query_result_product_name);
    }

    if(count($query_result_product_name)===2){
        $company_sid = $query_result_product_name[0]['company_sid'];
        $update_name = $query_result_product_name[0]['product_name'];
    }
    if($company_sid !== false && $company_sid !== __COMPANY_SID__){
        $is_update = false;
        $update_sid = '';
    }

    //************수정일 경우******************
    if($is_update === true){
        $title = 'BOM 수정 화면';
        $btn_text = '수정';
        $api_url = 'api_bom_update';

        //품목 테이블에서 품목sid, 품목명 가져온 후 매핑
        $search_product_sql = "SELECT product_name from product_info WHERE sid='$update_sid' AND company_sid = '".__COMPANY_SID__."';";
        $query_result_product = sql($search_product_sql);
        $query_result_product = select_process($query_result_product);

        $update_name = $query_result_product[0]['product_name'];

        //자재 테이블에서 자재sid, 자재명 가져온 후 매핑
        $search_material_sql = "SELECT sid, material_name from material_info WHERE company_sid = '".__COMPANY_SID__."'; ";
        $query_result_material = sql($search_material_sql);
        $query_result_material = select_process($query_result_material);

        $material_data = array();

        //품목 sid와 회사 sid로 bom조회
        $search_bom_sql = "SELECT sid ,material_sid ,ea ,bom_note from bom_info WHERE product_sid='$update_sid' AND company_sid = '".__COMPANY_SID__."';";
        $query_result_bom = sql($search_bom_sql);
        $query_result_bom = select_process($query_result_bom);

        for($i=0; $i < $query_result_bom['output_cnt']; $i++){

            $temp =array();
            $temp['material_key'] = $query_result_bom[$i]['material_sid'];
            $temp['material_cnt'] = $query_result_bom[$i]['ea'];
            $temp['note'] = $query_result_bom[$i]['bom_note'];

            array_push($material_data, $temp);

        }    

        echo '<script>var material_data = '.json_encode($material_data).';</script>';
           
    }

    //****************등록일 경우*******************    
    //품목 테이블에 회사코드를 조건으로 품목sid, 품목명 가져오기
    $select_product_sql = "SELECT sid,product_name from product_info where company_sid= '".__COMPANY_SID__."'; ";

    $query_result = sql($select_product_sql);
    $query_result = select_process($query_result);

    //배열 선언하기 (품목sid , 품목명)
    $product_sid = array();
    $product_name = array();

    //for문으로 품목sid, 품목명 push하기
    for($i=0; $i<$query_result['output_cnt']; $i++){

        array_push($product_sid, $query_result[$i]['sid']);

        $product_name[$query_result[$i]['sid']] = $query_result[$i]['product_name'];        

    }

    //Bom 테이블에 회사코드, 품목sid를 조회한다
    $select_bom_sql = "SELECT product_sid, company_sid from bom_info WHERE company_sid = '".__COMPANY_SID__."' GROUP BY product_sid , company_sid ";

    $query_result = sql($select_bom_sql);
    $query_result = select_process($query_result);

    $bom_product_sid = array();

    for($i = 0 ; $i < $query_result['output_cnt'];$i++ ){

        array_push($bom_product_sid,$query_result[$i]['product_sid']);
    }

    //품목sid 배열에서 bom 품목 sid를 차집합 해주기(diff,array_values)
    $not_registration = array();
    $not_registration = array_diff($product_sid, $bom_product_sid);
    $not_registration = array_values($not_registration);
    $not_registration_cnt = count($not_registration);

    $product_data = array();

    //bom등록이 안된 데이터 가공
    for($i = 0; $i < $not_registration_cnt; $i++){

        $temp = array();
        $temp['sid'] = $not_registration[$i];
        $temp['product_name'] = $product_name[$not_registration[$i]];
        array_push($product_data, $temp);

    }
    $product_data_cnt = count($product_data);
    
    //자재
    $select_material_sql = "SELECT sid, material_name FROM material_info WHERE company_sid='".__COMPANY_SID__."';";
    $query_result = sql($select_material_sql);
    $query_result = select_process($query_result);

    $material_list = array();

    $material_option = '<option value="" selected disabled>자재 선택</option>';

    for($i=0; $i<$query_result['output_cnt']; $i++){

        $material_option .= '<option value="'.$query_result[$i]['sid'].'">'.$query_result[$i]['material_name'].'</option>';
        array_push($material_list, $query_result[$i]);
    }
    
    echo '<script>var material_list = '.json_encode($material_list).';</script>';
    //종합한 것을 echo var로 보내서 이후 출력
    echo '<script> var product_sid="'.($update_sid ?? '').'";</script>';
    echo '<script> var is_update="'.($is_update ? 'true' : 'false').'";</script>';
?>

<section id="pp">
    <h2><?=$title?></h2>
    <h2><?=$update_name?></h2>

    <!-- *********품목선택******* -->
    <!-- 등록일 경우 -->
    <?php if($is_update === false) { ?>

        <select id='product_sid'>
            <option value="" selected disabled>품목선택</option>
            <?php
            for ($i = 0; $i < $product_data_cnt; $i++) {
            ?>
                <option value="<?=$product_data[$i]['sid']; ?>"><?=$product_data[$i]['product_name']; ?></option>
            <?php
            }
            ?>
        </select>
    <?php } ?>

    <!-- 수정일 경우 -->
    <?php if($is_update === true) { ?>

        <input type='hidden' id='product_sid' name="product_sid" value="<?= $update_sid?>"> 
        
    <?php } ?>

    <button onclick="registration('<?=$api_url?>');"><?=$btn_text?></button>
</section>
<hr>

<table id='grid_table'>
    <colgroup>
        <col width='300px' />
        <col width='100px' />
        <col width='100px' />
    </colgroup>
    <!-- 목록 출력 영역(헤드) -->
    <thead>
        <tr>
            <th>자재선택</th>
            <th>수량</th>
            <th>비고</th>
            <th style='display:none;'></th>
        </tr>
    </thead>

    <!-- 목록 바디 영역 -->
    <tbody>
    <!-- 수정일 경우 -->
    <?php if($is_update === true) { 
    	?>
       
        <?php
        for ($i = 0; $i < $query_result_bom['output_cnt']; $i++) {
        ?>
            <tr>
                <td>
                    <select key='material_key' onchange='is_onchange(this,0);'>
                        <?php
                        for ($j = 0; $j < $query_result_material['output_cnt']; $j++) {
                            $is_match = $query_result_bom[$i]['material_sid'] === $query_result_material[$j]['sid'];
                            $bom_material_sid = $query_result_bom[$i]['material_sid'];

                            if ($is_match === true) {
                        ?>
                                <option selected value="<?=$query_result_bom[$i]['material_sid']?>"><?=$query_result_material[$j]['material_name']?></option>
                            <?php 
                            } else { 
                            ?>
                                <option value="<?=$query_result_material[$j]['sid']?>"><?=$query_result_material[$j]['material_name']?></option>
                            <?php
                            }
                        } // for문 닫힘
                        ?>
                    </select>
                </td>
                <td>
                    <input  key='material_cnt'  type='number' onchange="is_onchange(this,0);" value='<?=$query_result_bom[$i]['ea']?>'/>
                </td>
                <td>
                    <input type='text' onchange="is_onchange(this,0);" key='note' value='<?=$query_result_bom[$i]['bom_note']?>'/>
                </td>
                <td>
                    <button onclick='row_add();'>추가</button>
                </td>
            </tr>
        <?php 
        }
        } else { //등록일 경우

        	?>
            <tr>
                <td>
                    <select key='material_key' onchange='is_onchange(this,0);'>
                        <?= $material_option ?>
                    </select>
                </td>
                <td>
                    <input type='number' onchange="is_onchange(this,0);" key='material_cnt' value='<?=$bom_ea?>'/>
                </td>
                <td>
                    <input type='text' onchange="is_onchange(this,0);" key='note' value='<?=$bom_note?>'/>
                </td>
                <td>
                    <button onclick='row_add();'>추가</button>
                </td>
            </tr>
        	<?php
        	 } //esle 닫힘
        	 ?>
    </tbody>
</table>
