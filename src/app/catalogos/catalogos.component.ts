import { AuthService } from './../shared/services/auth.service';
import { CatalogoService } from './catalogo.service';
import { Component, OnInit } from '@angular/core';
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

  constructor(
    private catalogoService: CatalogoService,
    private authService: AuthService
  ) {
    this.selectedCatalogoId = 0;
    this.catalogoService.getCatalogos()
    .then((response: any) => {
      if (environment.debug) { console.log(response); }
      if (response.fun.access) {
        this.catalogos = response.fun.ls;
        if (environment.debug) { console.log(this.catalogos); }
      } else {
        this.authService.executeAccess(response.fun.execute);
      }
    });
   }

  loadCatalogo(id, carga = false) {
    console.log(id);
    this.load = true;
    this.carga = carga;
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
  ngOnInit() {
  }

}
