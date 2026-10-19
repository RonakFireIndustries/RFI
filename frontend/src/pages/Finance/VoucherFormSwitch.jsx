import { useParams } from 'react-router-dom';
import VoucherFormPage from './VoucherFormPage';
import ItemVoucherFormPage from './ItemVoucherFormPage';
import { isItemVoucher } from '../../config/vouchers';

const VoucherFormSwitch = () => {
  const { type } = useParams();
  return isItemVoucher(type) ? <ItemVoucherFormPage /> : <VoucherFormPage />;
};

export default VoucherFormSwitch;