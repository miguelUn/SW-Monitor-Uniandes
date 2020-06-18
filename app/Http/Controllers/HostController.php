<?php

namespace App\Http\Controllers;

use Illuminate\Support\Collecion;
use Illuminate\Http\Request;

use App\Http\Requests\HostRequest;
use App\User;
use App\Model\Host;
use App\Model\HostType;
use App\Model\Responsable;
use Carbon\Carbon;

use App\Imports\HostsImport;
use Maatwebsite\Excel\Facades\Excel;

class HostController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
      $this->middleware('auth')->except(['index', 'show','sitiosWeb','servidores','database']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index(Host $host)
    {

          $host =  $host::orderBy('last_time_down', 'DESC')->paginate(80);

      return view('hosts.index', ['hosts' => $host]);
    }
    public function show($name){
      $host = Host::where('name','=', $name)->firstOrFail();
      return view('hosts.show', ['host' => $host]);
    }
    /**
     * Muestra el formulario para editar el host.
     *
     * @param  \App\Model\Host  $host
     * @return \Illuminate\View\hosts\edit
     */
    public function edit($name, HostType $typos)
    {
      $host = Host::where('name','=', $name)->firstOrFail();
      $servidor = Host::where('tipo_id','=', '2')->get();
      $servidorBD = Host::where('tipo_id','=', '3')->get();
      $typos = HostType::all();
      $users = User::all();
      $responsables = Responsable::where('host_id','=',$host->id)->get();
      // dd($responsables);
      return view('hosts.edit', [ 'host' => $host, 'servidores' => $servidor,'servidoresBD' => $servidorBD, 'typos' => $typos, 'users' => $users, 'responsables' => $responsables ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Model\Host  $host
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Host $host)
    {

        isset($request->mostrar)?$host->mostrar = $request->mostrar:$host->mostrar = "1";
        $host->servidor = $request->servidor;
        $host->servidor_bd = $request->servidor_bd;
        $host->analytics = $request->analytics;
        $host->description = $request->description;
        $host->tipo_id = $request->tipo;
        // $host->tipo_id = $request->responsable1;
        // dd($host->mostrar);
        $host->save();
        isset($request->responsable2)?'':$this->updateResponsable($host->id, $request->responsable2);
        isset($request->responsable1)?'':$this->updateResponsable($host->id, $request->responsable1);
        return redirect()->route('hosts')->withStatus(__('Host actualizado con éxito.'));
    }

    public function updateResponsable(int $host_id, int $user_id){
      $responsable = new Responsable;
      $responsable->host_id  = $host_id;
      $responsable->user_id  = $user_id;
      $responsable->save();
    }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function sitiosWeb(Host $host){
      $host =  $host::where('tipo_id','=',1)->orderBy('last_time_down', 'DESC')->paginate(80);
// Host::where('name','=', $name)->firstOrFail();
  return view('hosts.index', ['hosts' => $host]);
    }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function servidores(Host $host){
      $host =  $host::where('tipo_id','=',2)->orderBy('last_time_down', 'DESC')->paginate(80);

  return view('hosts.index', ['hosts' => $host]);
    }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function database(Host $host){
      $host =  $host::where('tipo_id','=',3)->orderBy('last_time_down', 'DESC')->paginate(80);

  return view('hosts.index', ['hosts' => $host]);
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function store()
    {
        Excel::import(new HostsImport,request()->file('file'));
        dd("hola");
        return back();
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function create()
    {
        return view('hosts.create');
    }

}
