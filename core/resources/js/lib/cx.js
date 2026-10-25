/** Joins the truthy class names. */
export default function cx(...classes) {
  return classes.filter(Boolean).join(' ')
}
