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
    this.selectedEmpleadoId = 0;
    this.empleadoService.getEmpleados()
    .then((response: any) => {
      if (environment.debug) { console.log(response); }
      if (response.fun.access) {
        this.empleados = response.fun.ls;
        if (environment.debug) { console.log(this.empleados); }
        this.loadEmpleado(12); // esta
        this.selectedEmpleadoId = 12; // esta
      } else {
        this.authService.executeAccess(response.fun.execute);
      }
    });
   }

   openModal(template: TemplateRef<any>) {
     this.modalRef = this.modalService.show(template);
   }
  ngOnInit() {
  }

}
