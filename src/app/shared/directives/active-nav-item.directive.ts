import { Directive, Input, ElementRef, AfterViewChecked } from '@angular/core';

@Directive({
  // tslint:disable-next-line:directive-selector
  selector: '[ActiveNavItem]'
})
export class ActiveNavItemDirective implements AfterViewChecked {
@Input() ActiveNavItem: string;
  constructor(
    private elementRef: ElementRef
  ) { }

  ngAfterViewChecked() {
    if (this.elementRef.nativeElement.getAttribute('id') === this.ActiveNavItem) {
      this.addActive();
    } else {
      this.elementRef.nativeElement.classList.remove('active');
    }
  }

  addActive() {
    this.elementRef.nativeElement.classList.add('active');
  }
}
