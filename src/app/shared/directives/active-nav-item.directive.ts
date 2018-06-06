import { Directive, Input, ElementRef, AfterViewInit } from '@angular/core';

@Directive({
  // tslint:disable-next-line:directive-selector
  selector: '[ActiveNavItem]'
})
export class ActiveNavItemDirective implements AfterViewInit {
@Input() ActiveNavItem: string;
  constructor(
    private elementRef: ElementRef
  ) { }

  ngAfterViewInit() {
    console.log(this.elementRef.nativeElement);
  }
}
