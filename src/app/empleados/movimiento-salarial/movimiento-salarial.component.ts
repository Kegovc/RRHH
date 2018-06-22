import { Component, OnInit, TemplateRef } from '@angular/core';
import { BsModalService, BsModalRef } from 'ngx-bootstrap';
import { environment } from './../../../environments/environment';
import { AuthService } from './../../shared/services/auth.service';
import { EmpleadoService } from './../empleado.service';

@Component({
  selector: 'app-movimiento-salarial',
  templateUrl: './movimiento-salarial.component.html',
  styleUrls: ['./movimiento-salarial.component.scss']
})
export class MovimientoSalarialComponent implements OnInit {
  public load = false;
  public data: any = {};
  public empleados: any[] = [];
  public selectedEmpleadoId: number;
  public carga =  false;

  public movimientos: any[] = [];
  public tipos: any[] = [];
  public mes: any[] = [
    {id: 1, descripcion: 'Enero'},
    {id: 2, descripcion: 'Febrero'},
    {id: 3, descripcion: 'Marzo'},
    {id: 4, descripcion: 'Abril'},
    {id: 5, descripcion: 'Mayo'},
    {id: 6, descripcion: 'Junio'},
    {id: 1, descripcion: 'Julio'},
    {id: 2, descripcion: 'Agosto'},
    {id: 3, descripcion: 'Septiembre'},
    {id: 4, descripcion: 'Octubre'},
    {id: 5, descripcion: 'Noviembre'},
    {id: 6, descripcion: 'Diciembre'}
  ];
  public anio: any[] = [];
  modalRef: BsModalRef;

  loadEmpleado(empleado, _carga = false) {
    if (environment.debug) { console.log(empleado); }
    this.carga = _carga;
    if (empleado > 0) {
      this.load = true;
      this.empleadoService.getMovimientos({index: empleado})
        .then((response: any) => {
          if (environment.debug) { console.log(response); }
          if (response.fun.access) {
            this.load = false;
            this.carga = true;
            this.movimientos = response.fun.ls.map((dato: any) => {
              const porcentual = `${dato.incremento_p}`.split('.');
              if (porcentual[1] === '00') {
                dato.incremento_p = porcentual[0];
              }
              return dato;
            });
            this.tipos = response.fun.tipos;
          }
        });
    } else {
      this.data = {};
    }
  }
  constructor(
    private empleadoService: EmpleadoService,
    private authService: AuthService,
    private modalService: BsModalService
  ) {
    const now = new Date();
    let index = 0;
    for (let i = 2000; i <= now.getFullYear() ; i++, index ++) {
      this.anio[index] = {id: (index + 1), descripcion: `${i}`};
    }
    this.data.mes_id = now.getMonth() + 1;
    this.data.anio_id = index;
    this.data.tipo_id = 1;
    this.data.incremento = 0;
    this.selectedEmpleadoId = 0;
    this.empleadoService.getEmpleados()
    .then((response: any) => {
      if (environment.debug) { console.log(response); }
      if (response.fun.access) {
        this.empleados = response.fun.ls;
        if (environment.debug) { console.log(this.empleados); }
      } else {
        this.authService.executeAccess(response.fun.execute);
      }
    });
   }
   aceptar() {
     if (environment.debug) { console.log(this.data); }
     this.data.id_emp = this.selectedEmpleadoId;
     this.data.mes = this.mes.find((dato: any) => dato.id === this.data.mes_id).descripcion;
     this.data.anio = this.anio.find((dato: any) => dato.id === this.data.anio_id).descripcion;
     this.empleadoService.setMovimiento(this.data)
     .then((response: any) => {
       if (environment.debug) { console.log(response); }
       if (response.fun.access) {
        this.loadEmpleado(this.selectedEmpleadoId, true);
        this.closeModal();
       }
     });
   }
   openModal(template: TemplateRef<any>) {
     this.modalRef = this.modalService.show(template, { ignoreBackdropClick: true});
   }
   closeModal() {
    this.modalRef.hide();
   }
  ngOnInit() {
  }

}
