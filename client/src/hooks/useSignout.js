import { useSignoutMutation } from "@/services/authApi";
import { useDispatch } from "react-redux";
import { clearAuth } from "@/lib/features/authSlice";
import { useRouter } from "next/navigation";
import { toast } from "react-hot-toast";

const useSignout = () => {
  const router = useRouter();
  const dispatch = useDispatch();
  const [signout] = useSignoutMutation();

  const handleSignout = async () => {
    try {
      await signout();
      dispatch(clearAuth());
      router.push('/');
    } catch (e) {
      toast.error(e.message);
    }
  };

  return { handleSignout };
};

export default useSignout;