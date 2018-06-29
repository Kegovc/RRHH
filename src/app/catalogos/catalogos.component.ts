import { BsModalService, BsModalRef } from 'ngx-bootstrap';
import { AuthService } from './../shared/services/auth.service';
import { CatalogoService } from './catalogo.service';
import { Component, OnInit, TemplateRef, ViewChild } from '@angular/core';
import { environment } from '../../environments/environment';

@Component({
  selector: 'app-catalogos',
  templateUrl: './catalogos.component.html',
  styleUrls: ['./catalogos.component.scss']
})
export class CatalogosComponent implements OnInit {
  public load = false;
  public data: any = {};
  public catalogos: any[] = [];
  public catalogo: any[] = [];
  public selectedCatalogoId: number;
  public carga =  false;

  @ViewChild('descripcion') descripcion: any;
  public selectedCatalogoName: string;
  config = {
    keyboard: false,
    ignoreBackdropClick: true
  };
  public btn = 'Agregar';
  modalRef: BsModalRef;
  constructor(
    private modalService: BsModalService,
    private catalogoService: CatalogoService,
    private authService: AuthService
  ) {
    this.selectedCatalogoId = 0;
    this.catalogoService.getCatalogos()
    .then((response: any) => {
      if (environment.debug) { console.log(response); }
      if (response.fun.access) {
        this.catalogos = response.fun.ls;
        this.selectedCatalogoId = 11;
        this.loadCatalogo(11);
        if (environment.debug) { console.log(this.catalogos); }
      } else {
        this.authService.executeAccess(response.fun.execute);
      }
    });
   }

  loadCatalogo(id, carga = false) {
    this.refresh();
    console.log(id);
    this.load = true;
    this.carga = carga;
    if (id > 0) {
      const selectedCatalogo = (this.catalogos.find((dato: any) => {
        if (dato.id === String(this.selectedCatalogoId)) { return dato; }
      }));
      this.selectedCatalogoName = selectedCatalogo.descripcion;
      this.catalogoService.getCatalogo(id)
      .then((response: any) => {
        if (environment.debug) { console.log(response); }
        if (response.fun.access) {
          this.catalogo = response.fun.ls;
          this.load = false;
          this.carga = true;
          if (environment.debug) { console.log(this.catalogo); }
        } else {
          this.authService.executeAccess(response.fun.execute);
        }
      });
    }
   }
   refresh() {
     this.data = {};
     this.data.id = 0;
     this.btn = 'Agregar';
   }
   accion(template: TemplateRef<any>) {
    console.log(this.data);
    if ('descripcion' in this.data && (this.data.descripcion !== '' || this.selectedCatalogoName === 'Dias Feriados')) {
      this.modalRef = this.modalService.show(template, this.config);
      const dato = this.data;
      if (this.selectedCatalogoName === 'Dias Feriados') {
        // tslint:disable-next-line:max-line-length
        dato.descripcion = `${this.data.descripcion.getFullYear()}-${this.data.descripcion.getMonth() + 1}-${this.data.descripcion.getDate()}`;
        console.log(dato.descripcion);
      }
      dato.id_catalogo = this.selectedCatalogoId;
      this.catalogoService.setCatalogo(dato)
      .then((response: any) => {
        if (environment.debug) { console.log(response); }
        if (response.fun.access) {
          this.loadCatalogo(this.selectedCatalogoId, true);
        }
        this.modalRef.hide();
      });
      } else {
        this.catalogoService.alerteFaltaDatos();
      }
   }
   modificar(id) {
     const dato_temp = this.catalogo.find((dato: any) => { if (dato.id === id) { return dato; }});
    this.data.id = dato_temp.id;
    this.data.descripcion = dato_temp.descripcion;
    if (this.selectedCatalogoName === 'Dias Feriados') {
      const s_fecha = this.data.descripcion.split('-');
      this.data.descripcion = new Date(s_fecha[0], s_fecha[1] - 1, s_fecha[2]);
    }
    this.btn = 'Modificar';
    this.descripcion.nativeElement.focus();
   }
  ngOnInit() {
  }

}
