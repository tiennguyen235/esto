<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\KhuyenMai;
use App\Models\CTKhuyenMai;
use App\Models\KhoaHoc;
use Illuminate\Http\Request;
use Carbon\Carbon;

class KhuyenMaiController extends Controller
{
    public function index()
    {
        $khuyenmai = KhuyenMai::paginate(10);      
        return view('admin.khuyenmai.index', ['khuyenmai'=>$khuyenmai]);
    }
    
    public function create()
    {
        $khoahoc = KhoaHoc::paginate(10);
        return view('admin.khuyenmai.create', ['khoahoc'=>$khoahoc]);
    }

    public function store(Request $request)
    {        
        $data = $request->all();   
        $dateBD = Carbon::createFromTimestamp(strtotime($data['NGAYBD'] . $data['TDBD'] . ":00"));      
        $dateKT = Carbon::createFromTimestamp(strtotime($data['NGAYKT'] . $data['TDKT'] . ":00"));   
        $today = date("Y-m-d h:i:sa");
            if(strtotime($dateBD) <=  strtotime($today) && strtotime($today) <= strtotime($dateKT))
            {
                $MATT = 1;
            }
            else
                $MATT = 2; 
        $result = $dateBD->lt($dateKT);
        $exits1 = KhuyenMai::Where('NGAYKT','>',$dateBD)->where('NGAYBD','<', $dateBD)->count();
        $exits2 = KhuyenMai::Where('NGAYBD','<',$dateKT)->where('NGAYKT','>', $dateKT)->count();
        
        if(KhuyenMai::where('TENKM', '=',$data['TENKM'])->count() < 1)
        {
            if ($result) 
            {
                if ($exits1 > 0 || $exits2 > 0) {
                        return redirect('admin/khuyenmai/them')->with('thatbai', 'Đã tồn tại khuyến mãi khác trong thời gian này.!');
                }
                else {
                    try {
                        $khuyenmai = KhuyenMai::create([
                            'TENKM' => $data['TENKM'],
                            'TYLEKM' => $data['TYLEKM'],
                            'NGAYBD' => $dateBD,
                            'NGAYKT' => $dateKT,
                            'MATT' => $MATT
                        ]);
                        $dskh = $data['danhsach'];
                        foreach ($dskh as $ds) {
                            CTKhuyenMai::create([
                            'MAKM' => $khuyenmai->MAKM,
                            'MAKH' => $ds
                            ]);                
                        }
                        return redirect('admin/khuyenmai/them')->with('thongbao', 'Thêm thành công!');
                    } catch (Exception $error) {
                        return redirect('admin/khuyenmai/them')->with('thongbao', 'Thêm thất bại.!');
                    }
                }
            }
            else
                return redirect('admin/khuyenmai/them')->with('thatbai', 'Thời gian kết phúc phải lớn hơn thời gian bắt đầu');
            
        }
        else    
            return redirect('admin/khuyenmai/them')->with('exits', 'Khuyến mãi này đã tồn tại');
    }

    public function edit($id)
    {
        $khoahoc = KhoaHoc::paginate(10);
        $khuyenmai = KhuyenMai::find($id);
        $ctkhuyenmai = CTKhuyenMai::where('MAKM', '=', $id)->get();
        return view('admin.khuyenmai.edit', compact('khoahoc','khuyenmai','ctkhuyenmai'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();   
        $dateBD = Carbon::createFromTimestamp(strtotime($data['NGAYBD'] . $data['TDBD'] . ":00"));      
        $dateKT = Carbon::createFromTimestamp(strtotime($data['NGAYKT'] . $data['TDKT'] . ":00"));   
        $today = date("Y-m-d h:i:sa");
            if(strtotime($dateBD) <=  strtotime($today) && strtotime($today) <= strtotime($dateKT))
            {
                $MATT = 1;
            }
            else
                $MATT = 2; 
        $result = $dateBD->lt($dateKT);
        // if (KhuyenMai::where('TENKM', '=',$data['TENKM'])->count() < 1) 
        // {
            if($result)
            {
                try {
                    $khuyenmai = KhuyenMai::find($id);
                    $khuyenmai->update(
                        [
                            'TENKM' => $data['TENKM'],
                            'TYLEKM' => $data['TYLEKM'],
                            'NGAYBD' => $dateBD,
                            'NGAYKT' => $dateKT,
                            'MATT' => $MATT
                        ]
                    );
                    $dskh = $data['danhsach'];
                    CTKhuyenMai::where('MAKM', $id)->delete();
                    foreach ($dskh as $ds)
                    {
                        CTKhuyenMai::create([
                            'MAKM' => $khuyenmai->MAKM,
                            'MAKH' => $ds
                        ]);                
                    }
                    return redirect('admin/khuyenmai/sua/' . $id)->with('thongbao', 'Sửa thành công!');
                } catch (Exception $error) {
                    return redirect('admin/khuyenmai/sua/' . $id)->with('thongbao', 'Sửa thất bại.!');
                }        
            }
            else
                return redirect('admin/khuyenmai/sua/' . $id)->with('thatbai', 'Thời gian kết phúc phải lớn hơn thời gian bắt đầu'); 
        // }
        // else
        //     return redirect('admin/khuyenmai/sua/' . $id)->with('exits', 'Khuyến mãi này đã tồn tại'); 
    }

    public function delete($id)
    {
        $khuyenmai = KhuyenMai::find($id);
        $khuyenmai->delete();

        return redirect('admin/khuyenmai/')->with('thongbao', 'Xóa thành công!');
    }

}
