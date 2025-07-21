<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\RencanaLatihan;
use Illuminate\Http\Request;
use App\Models\Atlet;
use App\Models\Pelatih;
use App\Models\TenagaPendukung;
use App\Models\MstPosisiAtlet;

class RencanaLatihanPesertaController extends Controller
{
    public function index(Request $request, $rencana_id, $jenis_peserta)
    {
        $rencana = RencanaLatihan::find($rencana_id);
        if (!$rencana) {
            return response()->json([
                'data' => [],
                'meta' => [
                    'total'        => 0,
                    'current_page' => 1,
                    'per_page'     => (int) $request->input('per_page', 10),
                ],
            ]);
        }

        $perPage = (int) $request->input('per_page', 10);
        $search  = $request->input('search', '');

        if ($jenis_peserta === 'atlet') {
            $caborKategoriId = $rencana->programLatihan->cabor_kategori_id;
            $query           = Atlet::query()
                ->join('rencana_latihan_atlet', function ($join) use ($rencana_id) {
                    $join->on('atlets.id', '=', 'rencana_latihan_atlet.atlet_id')
                         ->where('rencana_latihan_atlet.rencana_latihan_id', $rencana_id);
                })
                ->leftJoin('cabor_kategori_atlet', function ($join) use ($caborKategoriId) {
                    $join->on('atlets.id', '=', 'cabor_kategori_atlet.atlet_id')
                         ->where('cabor_kategori_atlet.cabor_kategori_id', $caborKategoriId)
                         ->whereNull('cabor_kategori_atlet.deleted_at');
                })
                ->select(
                    'atlets.id',
                    'atlets.nama',
                    'atlets.foto',
                    'atlets.jenis_kelamin',
                    'atlets.tempat_lahir',
                    'atlets.tanggal_lahir',
                    'atlets.no_hp',
                    'cabor_kategori_atlet.is_active as kategori_is_active',
                    'cabor_kategori_atlet.posisi_atlet_id',
                    'rencana_latihan_atlet.kehadiran as kehadiran',
                    'rencana_latihan_atlet.keterangan as keterangan'
                );
        } elseif ($jenis_peserta === 'pelatih') {
            $query = Pelatih::query()
                ->join('rencana_latihan_pelatih', function ($join) use ($rencana_id) {
                    $join->on('pelatihs.id', '=', 'rencana_latihan_pelatih.pelatih_id')
                         ->where('rencana_latihan_pelatih.rencana_latihan_id', $rencana_id);
                })
                ->select(
                    'pelatihs.id',
                    'pelatihs.nama',
                    'pelatihs.foto',
                    'pelatihs.jenis_kelamin',
                    'pelatihs.tempat_lahir',
                    'pelatihs.tanggal_lahir',
                    'pelatihs.no_hp',
                    'pelatihs.is_active',
                    'rencana_latihan_pelatih.kehadiran as kehadiran',
                    'rencana_latihan_pelatih.keterangan as keterangan'
                );
        } elseif ($jenis_peserta === 'tenaga-pendukung') {
            $query = TenagaPendukung::query()
                ->join('rencana_latihan_tenaga_pendukung', function ($join) use ($rencana_id) {
                    $join->on('tenaga_pendukungs.id', '=', 'rencana_latihan_tenaga_pendukung.tenaga_pendukung_id')
                         ->where('rencana_latihan_tenaga_pendukung.rencana_latihan_id', $rencana_id);
                })
                ->select(
                    'tenaga_pendukungs.id',
                    'tenaga_pendukungs.nama',
                    'tenaga_pendukungs.foto',
                    'tenaga_pendukungs.jenis_kelamin',
                    'tenaga_pendukungs.tempat_lahir',
                    'tenaga_pendukungs.tanggal_lahir',
                    'tenaga_pendukungs.no_hp',
                    'tenaga_pendukungs.is_active',
                    'rencana_latihan_tenaga_pendukung.kehadiran as kehadiran',
                    'rencana_latihan_tenaga_pendukung.keterangan as keterangan'
                );
        } else {
            return response()->json([
                'data' => [],
                'meta' => [
                    'total'        => 0,
                    'current_page' => 1,
                    'per_page'     => $perPage,
                ],
            ]);
        }

        if ($search) {
            $query->where($jenis_peserta === 'atlet' ? 'atlets.nama' : ($jenis_peserta === 'pelatih' ? 'pelatihs.nama' : 'tenaga_pendukungs.nama'), 'like', "%$search%");
        }

        $result = $query->paginate($perPage)->appends($request->all());

        // Mapping posisi atlet untuk response
        if ($jenis_peserta === 'atlet') {
            $data = $result->items();
            foreach ($data as &$row) {
                $row->posisi_atlet_nama = '-';
                if (!empty($row->posisi_atlet_id)) {
                    $posisi                 = MstPosisiAtlet::find($row->posisi_atlet_id);
                    $row->posisi_atlet_nama = $posisi ? $posisi->nama : '-';
                }
            }
            $result->setCollection(collect($data));
        }

        return response()->json([
            'data' => $result->items(),
            'meta' => [
                'total'        => $result->total(),
                'current_page' => $result->currentPage(),
                'per_page'     => $result->perPage(),
                'search'       => $search,
                'sort'         => $request->input('sort', ''),
                'order'        => $request->input('order', 'asc'),
            ],
        ]);
    }

    /**
     * Halaman daftar peserta rencana latihan (Inertia Page)
     */
    public function indexPage($program_id, $rencana_id, $jenis_peserta)
    {
        $rencana    = RencanaLatihan::with(['programLatihan.cabor', 'programLatihan.caborKategori'])->findOrFail($rencana_id);
        $program    = $rencana->programLatihan;
        $infoHeader = [
            'program_latihan_id'  => $program->id,
            'nama_program'        => $program->nama_program,
            'cabor_nama'          => $program->cabor?->nama,
            'cabor_kategori_nama' => $program->caborKategori?->nama,
            'cabor_kategori_id'   => $program->cabor_kategori_id,
            'periode_mulai'       => $program->periode_mulai,
            'periode_selesai'     => $program->periode_selesai,
        ];
        $infoRencana = [
            'tanggal'        => $rencana->tanggal,
            'materi'         => $rencana->materi,
            'lokasi_latihan' => $rencana->lokasi_latihan,
            'target_latihan' => $rencana->targetLatihan->pluck('deskripsi')->toArray(),
        ];
        return Inertia::render('modules/rencana-latihan/index/Index', [
            'program_id'    => $program_id,
            'rencana_id'    => $rencana_id,
            'jenis_peserta' => $jenis_peserta,
            'infoHeader'    => $infoHeader,
            'infoRencana'   => $infoRencana,
        ]);
    }

    /**
     * Hapus satu peserta dari rencana latihan
     */
    public function destroy(Request $request, $rencana_id, $jenis_peserta, $peserta_id)
    {
        $rencana = RencanaLatihan::findOrFail($rencana_id);
        if ($jenis_peserta === 'atlet') {
            $rencana->atlets()->detach($peserta_id);
        } elseif ($jenis_peserta === 'pelatih') {
            $rencana->pelatihs()->detach($peserta_id);
        } elseif ($jenis_peserta === 'tenaga-pendukung') {
            $rencana->tenagaPendukung()->detach($peserta_id);
        } else {
            return response()->json(['message' => 'Jenis peserta tidak valid'], 400);
        }
        return response()->json(['message' => 'Peserta berhasil dihapus']);
    }

    /**
     * Hapus banyak peserta dari rencana latihan
     */
    public function destroySelected(Request $request, $rencana_id, $jenis_peserta)
    {
        $ids     = $request->input('ids', []);
        $rencana = RencanaLatihan::findOrFail($rencana_id);
        if ($jenis_peserta === 'atlet') {
            $rencana->atlets()->detach($ids);
        } elseif ($jenis_peserta === 'pelatih') {
            $rencana->pelatihs()->detach($ids);
        } elseif ($jenis_peserta === 'tenaga-pendukung') {
            $rencana->tenagaPendukung()->detach($ids);
        } else {
            return response()->json(['message' => 'Jenis peserta tidak valid'], 400);
        }
        return response()->json(['message' => 'Peserta berhasil dihapus']);
    }

    /**
     * Set kehadiran massal untuk peserta
     */
    public function setKehadiran(Request $request, $rencana_id, $jenis_peserta)
    {
        $request->validate([
            'ids'       => 'required|array',
            'kehadiran' => 'required|in:Hadir,Tidak Hadir,Izin,Sakit',
            'keterangan' => 'nullable|string|max:1000',
        ]);
        $ids       = $request->input('ids');
        $kehadiran = $request->input('kehadiran');
        $keterangan = $request->input('keterangan');
        $rencana   = RencanaLatihan::findOrFail($rencana_id);

        if ($jenis_peserta === 'atlet') {
            foreach ($ids as $id) {
                $rencana->atlets()->updateExistingPivot($id, ['kehadiran' => $kehadiran
            , 'keterangan' => $keterangan ?? null]);
            }
        } elseif ($jenis_peserta === 'pelatih') {
            foreach ($ids as $id) {
                $rencana->pelatihs()->updateExistingPivot($id, ['kehadiran' => $kehadiran
            , 'keterangan' => $keterangan ?? null]);
            }
        } elseif ($jenis_peserta === 'tenaga-pendukung') {
            foreach ($ids as $id) {
                $rencana->tenagaPendukung()->updateExistingPivot($id, ['kehadiran' => $kehadiran
            , 'keterangan' => $keterangan ?? null]);
            }
        } else {
            return response()->json(['message' => 'Jenis peserta tidak valid'], 400);
        }
        return response()->json(['message' => 'Kehadiran berhasil diupdate']);
    }
}
